<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MenuRecommendationService;
use Inertia\Inertia;

class RecommendationController extends Controller
{
    public function __construct(private MenuRecommendationService $recommendationService) {}

    public function index()
    {
        $ranking = $this->recommendationService->buildRanking(onlyAvailable: false);

        return Inertia::render('Admin/Recommendations', [
            'criteria' => $ranking['criteria']->values(),
            'rows' => $ranking['rows']->values(),
            'unscoredMenus' => $ranking['unscoredMenus'],
            'ratings' => $this->recommendationService->getOverallRatingSummaries(),
        ]);
    }
}
