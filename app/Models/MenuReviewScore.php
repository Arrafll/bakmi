<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuReviewScore extends Model
{
    protected $fillable = [
        'menu_review_id',
        'assessment_criterion_id',
        'score',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function review()
    {
        return $this->belongsTo(MenuReview::class, 'menu_review_id');
    }

    public function criterion()
    {
        return $this->belongsTo(AssessmentCriterion::class, 'assessment_criterion_id');
    }
}
