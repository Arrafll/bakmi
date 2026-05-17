<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::where('is_available', true)->get();

        return Inertia::render('Menu', [
            'menus' => $menus,
        ]);
    }
}
