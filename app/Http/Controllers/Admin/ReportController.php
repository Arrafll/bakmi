<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Report');
    }

    public function data(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->date('start_date')->startOfDay();
        $endDate   = $request->date('end_date')->endOfDay();

        $orders = Order::with(['table:id,name', 'items.menu:id,name,price'])
            ->where('status', 'selesai')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $totalRevenue = $orders->sum('total_price');

        $rows = $orders->map(function (Order $order) {
            $itemsSummary = $order->items->map(function ($item) {
                return $item->menu->name . ' x' . $item->quantity;
            })->implode(', ');

            return [
                'id'              => $order->id,
                'customer_name'   => $order->customer_name,
                'customer_phone'  => $order->customer_phone ?? '-',
                'table'           => $order->table?->name ?? '-',
                'items'           => $itemsSummary ?: '-',
                'voucher_code'    => $order->voucher_code ?? '-',
                'discount_amount' => (float) $order->discount_amount,
                'total_price'     => (float) $order->total_price,
                'status'          => $order->status,
                'created_at'      => $order->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'orders'        => $rows,
            'total_revenue' => (float) $totalRevenue,
            'total_orders'  => $orders->count(),
            'start_date'    => $startDate->format('d/m/Y'),
            'end_date'      => $endDate->format('d/m/Y'),
        ]);
    }
}
