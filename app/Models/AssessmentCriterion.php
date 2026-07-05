<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentCriterion extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'direction',
        'weight',
        'scale_labels',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'scale_labels' => 'array',
        'is_active' => 'boolean',
        'weight' => 'integer',
        'sort_order' => 'integer',
    ];

    public function labelForScore(int $score): ?string
    {
        return $this->scale_labels[$score - 1] ?? null;
    }
}
