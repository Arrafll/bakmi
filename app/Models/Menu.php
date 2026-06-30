<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image_path',
        'category',
        'is_available',
    ];

    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sawScores()
    {
        return $this->hasMany(SawMenuScore::class);
    }
}
