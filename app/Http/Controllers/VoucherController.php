<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;

class VoucherController extends Controller
{
    /**
     * Validate a voucher code against the current cart total and return discount info.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code'        => 'required|string',
            'cart_total'  => 'required|numeric|min:0',
        ]);

        $voucher = Voucher::where('code', strtoupper($request->code))->first();

        if (! $voucher) {
            return response()->json(['error' => 'Kode voucher tidak ditemukan.'], 422);
        }

        if (! $voucher->isValid((float) $request->cart_total)) {
            $reason = 'Voucher tidak valid atau sudah kadaluarsa.';

            if ($request->cart_total < $voucher->min_order) {
                $reason = 'Minimum pembelian ' . number_format($voucher->min_order, 0, ',', '.') . ' untuk menggunakan voucher ini.';
            } elseif ($voucher->max_uses !== null && $voucher->used_count >= $voucher->max_uses) {
                $reason = 'Voucher sudah mencapai batas penggunaan.';
            }

            return response()->json(['error' => $reason], 422);
        }

        $discount = $voucher->calculateDiscount((float) $request->cart_total);

        return response()->json([
            'code'           => $voucher->code,
            'description'    => $voucher->description,
            'discount_type'  => $voucher->discount_type,
            'discount_value' => $voucher->discount_value,
            'discount_amount' => $discount,
        ]);
    }
}
