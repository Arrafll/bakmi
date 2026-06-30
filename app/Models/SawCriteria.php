<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SawCriteria extends Model
{
    protected $table = 'saw_criteria';

    protected $fillable = [
        'key',
        'name',
        'description',
        'type',
        'weight',
        'order',
        'is_active',
    ];

    protected $casts = [
        'weight'    => 'float',
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];
}
