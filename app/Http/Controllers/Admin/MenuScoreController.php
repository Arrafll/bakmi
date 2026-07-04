<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\MenuRecommendationService;
use Inertia\Inertia;

class MenuScoreController extends Controller
{
    public function __construct(private MenuRecommendationService $recommendationService) {}

    /**
     * Every criterion score shown here is computed, not entered: general
     * criteria are the average of customer questionnaire answers, and
     * Popularitas is derived from order frequency.
     */
    public function index()
    {
        $criteria = $this->recommendationService->getActiveCriteria();

        $menus = Menu::orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'image_path', 'is_available']);

        return Inertia::render('Admin/MenuScores', [
            'criteria' => $criteria,
            'menus' => $menus,
            'ratings' => $this->recommendationService->getRatingSummaries(),
            'popularity' => $this->recommendationService->getPopularityScores(),
        ]);
    }
}
