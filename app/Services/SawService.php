<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\OrderItem;
use App\Models\SawCriteria;
use App\Models\SawMenuScore;
use Illuminate\Support\Collection;

class SawService
{
    /**
     * Return all active criteria ordered by their display order.
     */
    public function getCriteria(): Collection
    {
        return SawCriteria::where('is_active', true)->orderBy('order')->get();
    }

    /**
     * Return all criteria (active + inactive) for management view.
     */
    public function getAllCriteria(): Collection
    {
        return SawCriteria::orderBy('order')->get();
    }

    /**
     * Return popularity scores for all menus derived from real order data.
     * Key: menu_id, Value: total quantity ordered.
     */
    public function getPopularityScores(): array
    {
        return OrderItem::selectRaw('menu_id, SUM(quantity) as total')
            ->groupBy('menu_id')
            ->pluck('total', 'menu_id')
            ->map(fn($v) => (float) $v)
            ->toArray();
    }

    /**
     * Build and return all data needed for the SAW process:
     *   criteria, menus, decision matrix, normalized matrix, preference values, ranking.
     */
    public function calculate(): array
    {
        $criteria   = $this->getCriteria();
        $menus      = Menu::where('is_available', true)
            ->with('sawScores')
            ->orderBy('name')
            ->get();

        if ($menus->isEmpty() || $criteria->isEmpty()) {
            return [
                'criteria'    => $criteria,
                'menus'       => $menus,
                'matrix'      => [],
                'normalized'  => [],
                'preferences' => [],
                'ranking'     => [],
            ];
        }

        $popularity = $this->getPopularityScores();

        // ── Step 1: Build decision matrix ────────────────────────────────────
        $matrix = [];
        foreach ($menus as $menu) {
            $row = [];
            foreach ($criteria as $criterion) {
                $row[$criterion->key] = $this->rawValue($menu, $criterion->key, $popularity);
            }
            $matrix[$menu->id] = $row;
        }

        // ── Step 2: Normalisation ─────────────────────────────────────────────
        $normalized = [];
        foreach ($criteria as $criterion) {
            $key    = $criterion->key;
            $values = array_column($matrix, $key);

            $max = max($values) ?: 1;
            $min = min($values) ?: 1;

            foreach ($menus as $menu) {
                $x = $matrix[$menu->id][$key];
                if ($criterion->type === 'benefit') {
                    $normalized[$menu->id][$key] = $max > 0 ? round($x / $max, 6) : 0;
                } else {
                    $normalized[$menu->id][$key] = $x > 0 ? round($min / $x, 6) : 0;
                }
            }
        }

        // ── Step 3: Weighted preference value V(i) = Σ w(j) * r(ij) ─────────
        $preferences = [];
        foreach ($menus as $menu) {
            $v = 0;
            foreach ($criteria as $criterion) {
                $v += $criterion->weight * ($normalized[$menu->id][$criterion->key] ?? 0);
            }
            $preferences[$menu->id] = round($v, 6);
        }

        // ── Step 4: Rank descending ───────────────────────────────────────────
        arsort($preferences);

        $ranking = [];
        $rank    = 1;
        foreach ($preferences as $menuId => $score) {
            $ranking[] = [
                'rank'   => $rank++,
                'menu'   => $menus->firstWhere('id', $menuId),
                'score'  => $score,
                'matrix' => $matrix[$menuId],
                'normalized' => $normalized[$menuId],
            ];
        }

        return [
            'criteria'    => $criteria,
            'menus'       => $menus,
            'matrix'      => $matrix,
            'normalized'  => $normalized,
            'preferences' => $preferences,
            'ranking'     => $ranking,
        ];
    }

    /**
     * Get raw decision-matrix value for a single criterion.
     * - price: taken directly from menus.price
     * - popularity: sum of quantity from order_items
     * - others: stored in saw_menu_scores
     */
    private function rawValue(Menu $menu, string $key, array $popularity): float
    {
        return match ($key) {
            'price'      => (float) $menu->price,
            'popularity' => (float) ($popularity[$menu->id] ?? 0),
            default      => (float) ($menu->sawScores->firstWhere('criteria_key', $key)?->score ?? 0),
        };
    }

    /**
     * Save updated criteria weights.
     * Expects ['key' => weight, ...] where weights must sum to 1.
     */
    public function updateWeights(array $weights): void
    {
        foreach ($weights as $key => $weight) {
            SawCriteria::where('key', $key)->update(['weight' => $weight]);
        }
    }

    /**
     * Save menu scores for a given menu.
     * Expects ['criteria_key' => score, ...] (only manual criteria).
     */
    public function saveMenuScores(int $menuId, array $scores): void
    {
        foreach ($scores as $key => $score) {
            SawMenuScore::updateOrCreate(
                ['menu_id' => $menuId, 'criteria_key' => $key],
                ['score' => (float) $score]
            );
        }
    }

    /**
     * Save scores for ALL menus at once (bulk).
     * Expects [menu_id => [criteria_key => score, ...], ...]
     */
    public function saveAllMenuScores(array $allScores): void
    {
        foreach ($allScores as $menuId => $scores) {
            $this->saveMenuScores((int) $menuId, $scores);
        }
    }

    /**
     * Gather per-menu scores keyed by menu_id, then criteria_key for the UI.
     */
    public function getMenuScoresMap(): array
    {
        $scores = SawMenuScore::all();
        $map    = [];
        foreach ($scores as $score) {
            $map[$score->menu_id][$score->criteria_key] = $score->score;
        }
        return $map;
    }

    /**
     * Return the top $limit ranked menus (for customer-facing view).
     */
    public function getTopMenus(int $limit = 5): array
    {
        $result = $this->calculate();
        return array_slice($result['ranking'], 0, $limit);
    }
}
