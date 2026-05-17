<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);

        return Inertia::render('Cart', [
            'cart' => array_values($cart),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'menu_id'  => 'required|integer|exists:menus,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $menu = Menu::findOrFail($request->menu_id);

        if (!$menu->is_available) {
            return back()->withErrors(['menu' => 'Menu tidak tersedia.']);
        }

        $cart = session('cart', []);
        $key  = $menu->id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = min($cart[$key]['quantity'] + $request->quantity, 99);
        } else {
            $cart[$key] = [
                'menu_id'  => $menu->id,
                'name'     => $menu->name,
                'price'    => (float) $menu->price,
                'image'    => $menu->image,
                'quantity' => $request->quantity,
            ];
        }

        session(['cart' => $cart]);

        return back();
    }

    public function update(Request $request)
    {
        $request->validate([
            'menu_id'  => 'required|integer',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $cart = session('cart', []);
        $key  = $request->menu_id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $request->quantity;
            session(['cart' => $cart]);
        }

        return back();
    }

    public function remove(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|integer',
        ]);

        $cart = session('cart', []);
        unset($cart[$request->menu_id]);
        session(['cart' => $cart]);

        return back();
    }

    public function clear()
    {
        session()->forget('cart');

        return back();
    }
}
