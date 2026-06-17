<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['session_id', 'menu_id', 'quantity'];

    protected $casts = ['quantity' => 'integer'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
