<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\CartItem;

class VoucherController extends Controller
{
    /**
     * Validate a voucher against the customer's actual server-side cart
     * total — a client-supplied total is never trusted — and report the
     * discount.
     *
     * Two customer-facing pages call this with different contracts: the QR
     * self-order page submits via Inertia and reads the result back from
     * flash data, while the standalone cart page calls it as a plain JSON
     * endpoint. Both are supported here.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string|max:50',
        ]);

        // Get session ID from table session
        $sessionId = $request->session()->get('table.session_id');

        if (!$sessionId) {
            return back()->with('error', 'Sesi tidak valid.');
        }

        // Get cart items from database
        $cartItems = CartItem::with('menu:id,price')
            ->where('session_id', $sessionId)
            ->get();

        $cartTotal = $cartItems->sum(fn($item) => ($item->menu->price ?? 0) * $item->quantity);

        $voucher = Voucher::where('code', strtoupper($request->voucher_code))->first();

        if (! $voucher) {
            return back()->with('error', 'Kode voucher tidak ditemukan.');
        }

        if (! $voucher->isValid($cartTotal)) {
            $reason = 'Voucher tidak valid atau sudah kadaluarsa.';

            if (! $voucher->is_active) {
                $reason = 'Voucher tidak aktif.';
            } elseif ($cartTotal < $voucher->min_order) {
                $reason = 'Minimum pembelian Rp ' . number_format($voucher->min_order, 0, ',', '.') . ' untuk menggunakan voucher ini.';
            } elseif ($voucher->max_uses !== null && $voucher->used_count >= $voucher->max_uses) {
                $reason = 'Voucher sudah mencapai batas penggunaan.';
            }

            return back()->with('error', $reason);
        }

        $discount = $voucher->calculateDiscount($cartTotal);

        return back()->with([
            'success' => 'Voucher berhasil diterapkan!',
            'discount' => $discount,
        ]);
    }

    private function respondWithError(Request $request, string $message)
    {
        if ($request->header('X-Inertia')) {
            return back()->with('error', $message);
        }

        return response()->json(['error' => $message], 422);
    }
}
