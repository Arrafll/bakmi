<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\SawService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpkController extends Controller
{
    public function __construct(private SawService $sawService) {}

    public function index()
    {
        $result      = $this->sawService->calculate();
        $scoresMap   = $this->sawService->getMenuScoresMap();
        $menus       = Menu::where('is_available', true)->orderBy('name')->get();
        $popularity  = $this->sawService->getPopularityScores();

        return Inertia::render('Admin/Spk', [
            'criteria'   => $this->sawService->getAllCriteria(),
            'menus'      => $menus,
            'scoresMap'  => $scoresMap,
            'popularity' => $popularity,
            'result'     => [
                'matrix'      => $result['matrix'],
                'normalized'  => $result['normalized'],
                'preferences' => $result['preferences'],
                'ranking'     => collect($result['ranking'])->map(fn($r) => [
                    'rank'       => $r['rank'],
                    'score'      => $r['score'],
                    'matrix'     => $r['matrix'],
                    'normalized' => $r['normalized'],
                    'menu'       => $r['menu'] ? [
                        'id'         => $r['menu']->id,
                        'name'       => $r['menu']->name,
                        'price'      => $r['menu']->price,
                        'category'   => $r['menu']->category,
                        'image_path' => $r['menu']->image_path,
                    ] : null,
                ]),
            ],
        ]);
    }

    public function updateWeights(Request $request)
    {
        $criteria = $this->sawService->getAllCriteria();
        $keys     = $criteria->pluck('key')->toArray();

        $rules = [];
        foreach ($keys as $key) {
            $rules["weights.{$key}"] = 'required|numeric|min:0|max:1';
        }

        $validated = $request->validate($rules);
        $weights   = $validated['weights'];

        $total = array_sum($weights);
        if (abs($total - 1.0) > 0.001) {
            return back()->withErrors(['weights' => 'Total bobot harus sama dengan 1 (100%). Saat ini: ' . round($total * 100, 1) . '%']);
        }

        $this->sawService->updateWeights($weights);

        return back()->with('success', 'Bobot kriteria berhasil disimpan.');
    }

    public function updateScores(Request $request)
    {
        $validated = $request->validate([
            'scores'              => 'required|array',
            'scores.*'            => 'array',
            'scores.*.taste'      => 'required|numeric|min:1|max:10',
            'scores.*.portion'    => 'required|numeric|min:1|max:10',
            'scores.*.preparation_time' => 'required|numeric|min:1|max:10',
            'scores.*.presentation'     => 'required|numeric|min:1|max:10',
        ]);

        $this->sawService->saveAllMenuScores($validated['scores']);

        return back()->with('success', 'Skor menu berhasil disimpan.');
    }
}
