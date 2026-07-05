<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCriterion;
use App\Services\MenuRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CriteriaController extends Controller
{
    public function __construct(private MenuRecommendationService $recommendationService) {}

    public function index()
    {
        return Inertia::render('Admin/Criteria', [
            'criteria' => $this->recommendationService->getAllCriteria(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        AssessmentCriterion::create($data);

        return back()->with('success', 'Kriteria penilaian berhasil ditambahkan.');
    }

    public function update(Request $request, AssessmentCriterion $criterion)
    {
        $data = $this->validateData($request, $criterion->id);

        if ($data['name'] !== $criterion->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $criterion->id);
        }

        $criterion->update($data);

        return back()->with('success', 'Kriteria penilaian berhasil diperbarui.');
    }

    public function destroy(AssessmentCriterion $criterion)
    {
        $criterion->delete();

        return back()->with('success', 'Kriteria penilaian berhasil dihapus.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:assessment_criteria,name' . ($ignoreId ? ",{$ignoreId}" : '')],
            'direction' => ['required', 'in:benefit,cost'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'scale_labels' => ['required', 'array', 'size:5'],
            'scale_labels.*' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (
            AssessmentCriterion::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
