<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Voucher;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'notes'          => 'nullable|string|max:500',
            'voucher_code'   => 'nullable|string|max:50',
        ]);

        $tableContext = $request->session()->get('table');
        $sessionId    = $tableContext['session_id'] ?? null;

        $cartItems = $sessionId
            ? CartItem::with('menu:id,name,price')->where('session_id', $sessionId)->get()
            : collect();

        if ($cartItems->isEmpty()) {
            return back()->withErrors(['cart' => 'Keranjang kosong.']);
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->menu->price * $item->quantity);

        $discountAmount = 0;
        $voucherCode    = null;

        if ($request->filled('voucher_code')) {
            $voucher = Voucher::where('code', strtoupper($request->voucher_code))->first();

            if ($voucher && $voucher->isValid($subtotal)) {
                $discountAmount = $voucher->calculateDiscount($subtotal);
                $voucherCode    = $voucher->code;
                $voucher->increment('used_count');
            }
        }

        $total = max(0, $subtotal - $discountAmount);

        $order = Order::create([
            'table_id'         => $tableContext['id'] ?? null,
            'table_session_id' => $sessionId,
            'customer_name'    => $request->customer_name,
            'customer_phone'   => $request->customer_phone,
            'notes'            => $request->notes,
            'voucher_code'     => $voucherCode,
            'discount_amount'  => $discountAmount,
            'total_price'      => $total,
            'status'           => 'dipesan',
        ]);

        foreach ($cartItems as $item) {
            $order->items()->create([
                'menu_id'  => $item->menu_id,
                'quantity' => $item->quantity,
                'price'    => (float) $item->menu->price,
            ]);
        }

        // Clear DB cart for this session
        if ($sessionId) {
            CartItem::where('session_id', $sessionId)->delete();
        }

        return redirect()->route('order.success', $order->id);
    }

    public function success($id)
    {
        $order = Order::with('items.menu')->findOrFail($id);

        return Inertia::render('OrderSuccess', [
            'order' => $order,
        ]);
    }
}
