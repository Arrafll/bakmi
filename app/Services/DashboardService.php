<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Menu;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(): array
    {
        $today = now()->toDateString();

        return [
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'completed_orders_today' => Order::whereDate('created_at', $today)->where('status', 'selesai')->count(),
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

    public function getWeeklyOrders(): array
    {
        $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $startOfWeek = now()->subDays(6)->startOfDay();

        $orders = Order::select(
            DB::raw('DATE(created_at) as order_date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startOfWeek)
            ->groupBy('order_date')
            ->get()
            ->keyBy('order_date');

        $result = [];
        $currentDate = $startOfWeek->copy();

        for ($i = 0; $i < 7; $i++) {
            $dateKey = $currentDate->toDateString();
            $dayLabel = $days[$currentDate->dayOfWeek];
            $count = $orders->has($dateKey) ? $orders[$dateKey]->count : 0;

            $result[] = [
                'day' => $dayLabel,
                'count' => $count
            ];

            $currentDate->addDay();
        }

        return $result;
    }
}
