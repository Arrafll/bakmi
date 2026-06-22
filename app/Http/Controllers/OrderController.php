<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\OrderService;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'notes'          => 'nullable|string|max:500',
            'voucher_code'   => 'nullable|string|max:50',
        ]);

        $tableContext = $request->session()->get('table');

        try {
            $order = $this->orderService->createOrder($validatedData, $tableContext);
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }

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
