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

        // Pass table context via session flash for the success page
        return redirect()
            ->route('order.success', $order->id)
            ->with('table_context', $tableContext);
    }

    public function success($id)
    {
        $order = Order::with('items.menu')->findOrFail($id);

        // Get table context from flash or session
        $tableContext = session('table_context') ?? session('table');

        // If we have a table ID, fetch the QR token from database
        if ($tableContext && isset($tableContext['id'])) {
            $table = \App\Models\RestaurantTable::find($tableContext['id']);
            if ($table) {
                $tableContext['qr_token'] = $table->qr_token;
            }
        }

        return Inertia::render('OrderSuccess', [
            'order' => $order,
            'table' => $tableContext,
        ]);
    }
}
