<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSuccessfulTransactions(array $filters = [])
    {
        $query = Order::with(['items.menu', 'table', 'voucher'])
            ->where('status', 'completed');

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['table_id'])) {
            $query->where('table_id', $filters['table_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getReportData(array $filters = [])
    {
        $query = Order::with(['items.menu', 'table', 'voucher'])
            ->where('status', 'completed');

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['table_id'])) {
            $query->where('table_id', $filters['table_id']);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $data = [];
        $totalRevenue = 0;

        foreach ($orders as $order) {
            $orderData = [
                'order_id' => $order->id,
                'order_number' => $order->order_number ?? 'ORD-' . $order->id,
                'table' => $order->table ? $order->table->name : '-',
                'date' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $order->items->map(function ($item) {
                    return $item->menu->name . ' x' . $item->quantity;
                })->join(', '),
                'subtotal' => $order->total_price + ($order->discount_amount ?? 0),
                'voucher' => $order->voucher_code ?? '-',
                'discount' => $order->discount_amount ?? 0,
                'final_price' => $order->total_price,
                'payment_method' => $order->payment_method ?? 'cash',
            ];

            $data[] = $orderData;
            $totalRevenue += $order->total_price;
        }

        return [
            'data' => $data,
            'total_revenue' => $totalRevenue,
            'filters' => $filters
        ];
    }

    public function getTotalRevenue(array $filters = [])
    {
        $query = Order::where('status', 'completed');

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['table_id'])) {
            $query->where('table_id', $filters['table_id']);
        }

        return $query->sum('total_price');
    }
}
