<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuReview;
use App\Services\MenuRecommendationService;
use Inertia\Inertia;

class ReviewSubmissionController extends Controller
{
    public function __construct(private MenuRecommendationService $recommendationService) {}

    /**
     * Every customer questionnaire submission on record, plus the computed
     * per-menu, per-criterion scores derived from all of them.
     */
    public function index()
    {
        $criteria = $this->recommendationService->getCustomerAnswerableCriteria();

        $submissions = MenuReview::with([
                'order:id,customer_name,created_at',
                'menu:id,name',
                'scores.criterion:id,name',
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        $summary = $this->recommendationService->getRatingSummaries();
        $menuNames = Menu::whereIn('id', $summary->keys())->pluck('name', 'id');

        return Inertia::render('Admin/ReviewSubmissions', [
            'criteria' => $criteria,
            'submissions' => $submissions,
            'summary' => $summary,
            'menuNames' => $menuNames,
        ]);
    }
}
