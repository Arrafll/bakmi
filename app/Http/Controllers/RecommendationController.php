<?php

namespace App\Http\Controllers;

use App\Services\SawService;
use Inertia\Inertia;

class RecommendationController extends Controller
{
    public function __construct(private SawService $sawService) {}

    public function index()
    {
        $top = $this->sawService->getTopMenus(5);

        $ranking = collect($top)->map(fn($r) => [
            'rank'       => $r['rank'],
            'score'      => $r['score'],
            'menu'       => $r['menu'] ? [
                'id'          => $r['menu']->id,
                'name'        => $r['menu']->name,
                'description' => $r['menu']->description,
                'price'       => $r['menu']->price,
                'category'    => $r['menu']->category,
                'image_path'  => $r['menu']->image_path,
                'is_available'=> $r['menu']->is_available,
            ] : null,
        ])->filter(fn($r) => $r['menu'] !== null)->values();

        return Inertia::render('Recommendation', [
            'ranking'  => $ranking,
            'criteria' => $this->sawService->getCriteria(),
        ]);
    }
}
