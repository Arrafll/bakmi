<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category',
        'is_available',
    ];

    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
