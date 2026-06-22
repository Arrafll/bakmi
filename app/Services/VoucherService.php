<?php

namespace App\Services;

use App\Models\Voucher;

class VoucherService
{
    public function getAllVouchers()
    {
        return Voucher::orderByDesc('created_at')->get();
    }

    public function createVoucher(array $validatedData): Voucher
    {
        $data = $validatedData;
        $data['code'] = strtoupper($data['code']);

        return Voucher::create($data);
    }

    public function updateVoucher(Voucher $voucher, array $validatedData): Voucher
    {
        $voucher->update($validatedData);

        return $voucher;
    }

    public function deleteVoucher(Voucher $voucher): bool
    {
        return $voucher->delete();
    }
}
