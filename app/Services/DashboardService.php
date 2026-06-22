<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Menu;
use App\Models\Voucher;

class DashboardService
{
    public function getStats(): array
    {
        $today = now()->toDateString();

        return [
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'revenue_today' => Order::whereDate('created_at', $today)->sum('total_price'),
            'active_menus' => Menu::where('is_available', true)->count(),
            'active_vouchers' => Voucher::where('is_active', true)->count(),
        ];
    }

    public function getRecentOrders(int $limit = 10)
    {
        return Order::orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'customer_name', 'total_price', 'voucher_code', 'status', 'created_at']);
    }
}
