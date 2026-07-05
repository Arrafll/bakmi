<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuReview extends Model
{
    protected $fillable = [
        'order_id',
        'menu_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function scores()
    {
        return $this->hasMany(MenuReviewScore::class);
    }
}
