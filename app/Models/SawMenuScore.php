<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SawMenuScore extends Model
{
    protected $table = 'saw_menu_scores';

    protected $fillable = [
        'menu_id',
        'criteria_key',
        'score',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
