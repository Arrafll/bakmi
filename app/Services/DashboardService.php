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
            DB::raw('DAYNAME(created_at) as day_name'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startOfWeek)
            ->groupBy('day_name')
            ->get()
            ->keyBy('day_name');

        $result = [];
        $currentDate = $startOfWeek->copy();

        for ($i = 0; $i < 7; $i++) {
            $dayName = $currentDate->format('D');
            $dayLabel = $days[$currentDate->dayOfWeek];
            $count = $orders->has($dayName) ? $orders[$dayName]->count : 0;

            $result[] = [
                'day' => $dayLabel,
                'count' => $count
            ];

            $currentDate->addDay();
        }

        return $result;
    }
// <<<<<<< Updated upstream

    public function getFavoriteMenus(int $limit = 5): array
    {
        $favorites = OrderItem::select('menu_id', DB::raw('SUM(quantity) as total_ordered'))
            ->with('menu:id,name,category')
            ->groupBy('menu_id')
            ->orderByDesc('total_ordered')
            ->limit($limit)
            ->get();

        $totalQuantity = $favorites->sum('total_ordered');

        if ($totalQuantity === 0) {
            return [];
        }

        $colors = ['bg-amber-500', 'bg-blue-400', 'bg-green-400', 'bg-purple-400', 'bg-red-400'];

        return $favorites->map(function ($item, $index) use ($totalQuantity, $colors) {
            $percent = round(($item->total_ordered / $totalQuantity) * 100);

            return [
                'label' => $item->menu->name,
                'percent' => $percent,
                'color' => $colors[$index % count($colors)],
                'total_ordered' => $item->total_ordered
            ];
        })->toArray();
    }
// =======
// >>>>>>> Stashed changes
}
