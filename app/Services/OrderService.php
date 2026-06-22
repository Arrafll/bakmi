<?php

namespace App\Services;

use App\Models\Order;
use App\Models\CartItem;
use App\Models\Voucher;
use App\Events\NewOrderCreated;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function createOrder(array $validatedData, ?array $tableContext): Order
    {
        $sessionId = $tableContext['session_id'] ?? null;

        $cartItems = $sessionId
            ? CartItem::with('menu:id,name,price')->where('session_id', $sessionId)->get()
            : collect();

        if ($cartItems->isEmpty()) {
            throw new \Exception('Keranjang kosong.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->menu->price * $item->quantity);

        $discountAmount = 0;
        $voucherCode    = null;

        if (!empty($validatedData['voucher_code'])) {
            $voucher = Voucher::where('code', strtoupper($validatedData['voucher_code']))->first();

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
            'customer_name'    => $validatedData['customer_name'],
            'customer_phone'   => $validatedData['customer_phone'],
            'notes'            => $validatedData['notes'] ?? null,
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

        // Broadcast new order event to admin
        try {
            broadcast(new NewOrderCreated($order));
            Log::info('Order broadcast event dispatched', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Failed to broadcast order event', ['error' => $e->getMessage()]);
        }

        return $order;
    }
}
