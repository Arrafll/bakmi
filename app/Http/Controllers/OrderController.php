<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        $cart = session('cart', []);

        if (empty($cart)) {
            return back()->withErrors(['cart' => 'Keranjang kosong.']);
        }

        $subtotal = (float) array_sum(
            array_map(fn($item) => $item['price'] * $item['quantity'], $cart)
        );

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
            'customer_name'   => $request->customer_name,
            'customer_phone'  => $request->customer_phone,
            'notes'           => $request->notes,
            'voucher_code'    => $voucherCode,
            'discount_amount' => $discountAmount,
            'total_price'     => $total,
            'status'          => 'pending',
        ]);

        foreach ($cart as $item) {
            $order->items()->create([
                'menu_id'  => $item['menu_id'],
                'quantity' => $item['quantity'],
                'price'    => $item['price'],
            ]);
        }

        session()->forget('cart');

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
