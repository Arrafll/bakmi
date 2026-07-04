<?php

namespace App\Services;

use App\Models\AssessmentCriterion;
use App\Models\Menu;
use App\Models\MenuReviewScore;
use App\Models\OrderItem;
use Illuminate\Support\Collection;

class MenuRecommendationService
{
    public function getAllCriteria(): Collection
    {
        return AssessmentCriterion::orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * Average customer answer (1–5) and response count per menu, per
     * criterion, gathered from the post-order evaluation questionnaire. This
     * average (rounded) is what actually drives each general criterion's
     * score in the ranking — there is no manual entry anymore.
     */
    public function getRatingSummaries(): Collection
    {
        return MenuReviewScore::query()
            ->join('menu_reviews', 'menu_reviews.id', '=', 'menu_review_scores.menu_review_id')
            ->selectRaw('menu_reviews.menu_id as menu_id, menu_review_scores.assessment_criterion_id as criterion_id, ROUND(AVG(menu_review_scores.score), 1) as average, COUNT(*) as count')
            ->groupBy('menu_reviews.menu_id', 'menu_review_scores.assessment_criterion_id')
            ->get()
            ->groupBy('menu_id')
            ->map(fn (Collection $rows) => $rows->keyBy('criterion_id'));
    }

    /**
     * Same customer feedback, flattened into one overall average per menu
     * (across every criterion answered) for a simple at-a-glance reference.
     */
    public function getOverallRatingSummaries(): Collection
    {
        return MenuReviewScore::query()
            ->join('menu_reviews', 'menu_reviews.id', '=', 'menu_review_scores.menu_review_id')
            ->selectRaw('menu_reviews.menu_id as menu_id, ROUND(AVG(menu_review_scores.score), 1) as average, COUNT(*) as count')
            ->groupBy('menu_reviews.menu_id')
            ->get()
            ->keyBy('menu_id');
    }

    public function getActiveCriteria(): Collection
    {
        return AssessmentCriterion::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Active criteria the customer questionnaire actually asks about.
     * Popularitas is excluded because it's derived automatically from order
     * frequency instead of being answered by anyone.
     */
    public function getCustomerAnswerableCriteria(): Collection
    {
        return $this->getActiveCriteria()
            ->reject(fn (AssessmentCriterion $c) => $c->slug === 'popularitas')
            ->values();
    }

    /**
     * Popularity score (1–5) per menu, derived from how often each menu has
     * been ordered relative to the single most-ordered menu in the catalog.
     * A menu that's never been ordered scores 1; the most-ordered menu
     * always scores 5. Returns an empty collection if nothing has ever been
     * ordered (nothing to compare against yet).
     */
    public function getPopularityScores(): Collection
    {
        $orderCounts = OrderItem::selectRaw('menu_id, COUNT(*) as order_count')
            ->groupBy('menu_id')
            ->pluck('order_count', 'menu_id');

        $maxCount = $orderCounts->max();

        if (! $maxCount) {
            return collect();
        }

        return Menu::pluck('id')->mapWithKeys(function ($menuId) use ($orderCounts, $maxCount) {
            $count = (int) ($orderCounts[$menuId] ?? 0);
            $score = (int) min(5, max(1, round(1 + 4 * ($count / $maxCount))));

            return [$menuId => ['order_count' => $count, 'score' => $score]];
        });
    }

    /**
     * Builds the full scoring breakdown for every menu that has an automatically
     * computed value for each active criterion: general criteria are the rounded
     * average of customer questionnaire answers, Popularitas is derived from
     * order frequency. Also returns the comparable (0–1) value each raw score
     * converts to, each criterion's contribution once weighted, and the
     * resulting rank.
     */
    public function buildRanking(bool $onlyAvailable = true): array
    {
        $criteria = $this->getActiveCriteria();

        if ($criteria->isEmpty()) {
            return [
                'criteria' => $criteria,
                'rows' => collect(),
                'unscoredMenus' => collect(),
            ];
        }

        $popularityCriterion = $criteria->first(fn (AssessmentCriterion $c) => $c->slug === 'popularitas');
        $generalCriteria = $popularityCriterion
            ? $criteria->reject(fn (AssessmentCriterion $c) => $c->id === $popularityCriterion->id)
            : $criteria;

        $weightTotal = $criteria->sum('weight') ?: 1;

        $menuQuery = Menu::orderBy('name');

        if ($onlyAvailable) {
            $menuQuery->where('is_available', true);
        }

        $menus = $menuQuery->get();

        $ratingSummaries = $this->getRatingSummaries();
        $popularityScores = $popularityCriterion ? $this->getPopularityScores() : collect();

        // raw[menu_id][criterion_id] = computed score (1–5); only set once
        // there's enough data (customer answers, or any order history) to derive it.
        $raw = [];
        foreach ($menus as $menu) {
            $menuRatings = $ratingSummaries->get($menu->id);

            foreach ($generalCriteria as $criterion) {
                $summary = $menuRatings?->get($criterion->id);

                if ($summary) {
                    $raw[$menu->id][$criterion->id] = (int) min(5, max(1, round($summary->average)));
                }
            }

            if ($popularityCriterion) {
                $popularityData = $popularityScores->get($menu->id);
                $raw[$menu->id][$popularityCriterion->id] = $popularityData['score'] ?? 1;
            }
        }

        [$eligible, $unscored] = $menus->partition(
            fn (Menu $menu) => $generalCriteria->every(fn ($c) => isset($raw[$menu->id][$c->id]))
        );

        if ($eligible->isEmpty()) {
            return [
                'criteria' => $criteria,
                'rows' => collect(),
                'unscoredMenus' => $unscored->values(),
            ];
        }

        // Best/worst value in each criterion column, needed to make scores comparable.
        $best = [];
        $worst = [];
        foreach ($criteria as $criterion) {
            $columnValues = array_map(fn ($menu) => $raw[$menu->id][$criterion->id], $eligible->all());
            $best[$criterion->id] = max($columnValues);
            $worst[$criterion->id] = min($columnValues);
        }

        $rows = $eligible->map(function (Menu $menu) use ($criteria, $raw, $best, $worst, $weightTotal) {
            $comparable = [];
            $contribution = [];
            $finalScore = 0;

            foreach ($criteria as $criterion) {
                $value = $raw[$menu->id][$criterion->id];

                $comparableValue = $criterion->direction === 'benefit'
                    ? ($best[$criterion->id] > 0 ? $value / $best[$criterion->id] : 0)
                    : ($value > 0 ? $worst[$criterion->id] / $value : 0);

                $criterionWeight = $criterion->weight / $weightTotal;
                $weightedValue = $comparableValue * $criterionWeight;

                $comparable[$criterion->id] = $comparableValue;
                $contribution[$criterion->id] = $weightedValue;
                $finalScore += $weightedValue;
            }

            return [
                'menu' => $menu,
                'raw' => $raw[$menu->id],
                'comparable' => $comparable,
                'contribution' => $contribution,
                'score' => $finalScore,
                'percentage' => round($finalScore * 100, 1),
            ];
        })->sortByDesc('score')->values();

        $rows = $rows->map(function ($row, $index) {
            $row['rank'] = $index + 1;

            return $row;
        });

        return [
            'criteria' => $criteria,
            'rows' => $rows,
            'unscoredMenus' => $unscored->values(),
        ];
    }

    /**
     * Lightweight, customer-facing shortlist of the best-scoring available menus.
     */
    public function getTopRecommendations(int $limit = 5): array
    {
        $ranking = $this->buildRanking(onlyAvailable: true);

        return $ranking['rows']->take($limit)->map(function ($row) {
            $menu = $row['menu'];

            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'description' => $menu->description,
                'price' => $menu->price,
                'category' => $menu->category,
                'image_path' => $menu->image_path,
                'is_available' => $menu->is_available,
                'rank' => $row['rank'],
                'percentage' => $row['percentage'],
            ];
        })->values()->all();
    }
}
