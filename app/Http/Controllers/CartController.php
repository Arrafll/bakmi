<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Menu;
use Inertia\Inertia;

class CartController extends Controller
{
    /** Resolve the table_session_id or abort 403. */
    private function sessionId(Request $request): string
    {
        return $request->session()->get('table.session_id')
            ?? abort(403, 'No table session.');
    }

    /** Return cart rows for the current session as a plain array. */
    public static function cartForSession(string $sessionId): array
    {
        return CartItem::with('menu:id,name,price,image_path,is_available')
            ->where('session_id', $sessionId)
            ->get()
            ->map(fn ($row) => [
                'menu_id'  => $row->menu_id,
                'name'     => $row->menu?->name ?? '',
                'price'    => (float) ($row->menu?->price ?? 0),
                'image'    => $row->menu?->image,
                'quantity' => $row->quantity,
            ])
            ->values()
            ->all();
    }

    public function index(Request $request)
    {
        $sessionId = $this->sessionId($request);

        return Inertia::render('Cart', [
            'cart' => self::cartForSession($sessionId),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'menu_id'  => 'required|integer|exists:menus,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $menu = Menu::findOrFail($request->menu_id);

        if (! $menu->is_available) {
            return back()->withErrors(['menu' => 'Menu tidak tersedia.']);
        }

        $sessionId = $this->sessionId($request);

        // firstOrCreate with quantity=0 so the increment always works correctly
        $item = CartItem::firstOrCreate(
            ['session_id' => $sessionId, 'menu_id' => $menu->id],
            ['quantity'   => 0]
        );

        $item->quantity = min($item->quantity + $request->quantity, 99);
        $item->save();

        return back();
    }

    public function update(Request $request)
    {
        $request->validate([
            'menu_id'  => 'required|integer',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $sessionId = $this->sessionId($request);

        CartItem::where('session_id', $sessionId)
            ->where('menu_id', $request->menu_id)
            ->update(['quantity' => $request->quantity]);

        return back();
    }

    public function remove(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|integer',
        ]);

        $sessionId = $this->sessionId($request);

        CartItem::where('session_id', $sessionId)
            ->where('menu_id', $request->menu_id)
            ->delete();

        return back();
    }

    public function clear(Request $request)
    {
        $sessionId = $this->sessionId($request);

        CartItem::where('session_id', $sessionId)->delete();

        return back();
    }
}
