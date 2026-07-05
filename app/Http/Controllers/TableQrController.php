<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Services\MenuRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TableQrController extends Controller
{
    public function __construct(private MenuRecommendationService $recommendationService) {}

    /**
     * QR Entry Point: GET /order/{qr_token}
     *
     * Security layers applied here:
     *  1. Token validated against DB – prevents enumeration (no sequential IDs)
     *  2. Session regenerated – prevents session fixation
     *  3. Table ID stored only in session – never exposed in frontend URL/state
     *  4. Rate limited by RateLimiter::for('qr-scan') defined in AppServiceProvider
     */
    public function enter(Request $request, string $qrToken): Response
    {
        abort_unless(
            preg_match('/^[A-Za-z0-9]{32,64}$/', $qrToken) === 1,
            404
        );

        $table = RestaurantTable::where('qr_token', $qrToken)
            ->where('is_active', true)
            ->firstOrFail();

        // If the session already belongs to this exact table, just re-render
        // without touching the session or cart – handles browser refresh.
        if ($request->session()->get('table.id') === $table->id) {
            return Inertia::render('Order/Index', [
                'table' => [
                    'name'      => $table->name,
                    'sessionId' => $request->session()->get('table.session_id'),
                ],
                'menus'      => Menu::orderBy('category')
                    ->orderBy('name')
                    ->get(['id', 'name', 'description', 'price', 'category', 'image_path', 'is_available']),
                'categories' => Category::orderBy('name')->pluck('name'),
                'recommendations' => $this->recommendationService->getTopRecommendations(5),
                'pendingReview' => $this->resolvePendingReview($request->session()->get('table.session_id')),
            ]);
        }

        // ── New scan (different table or no session) ──────────────────────────
        // Capture OLD session_id BEFORE regenerating so we can clean up its cart.
        $oldSessionId = $request->session()->get('table.session_id');

        // Regenerate the session ID to prevent session fixation.
        $request->session()->regenerate();

        $tableSessionId = (string) Str::uuid();

        $request->session()->put('table', [
            'id'         => $table->id,
            'name'       => $table->name,
            'session_id' => $tableSessionId,
        ]);

        // Delete the old cart now that the new session is established.
        if ($oldSessionId) {
            CartItem::where('session_id', $oldSessionId)->delete();
        }

        return Inertia::render('Order/Index', [
            'table' => [
                'name'      => $table->name,
                'sessionId' => $tableSessionId,
            ],
            'menus'      => Menu::orderBy('category')
                ->orderBy('name')
                ->get(['id', 'name', 'description', 'price', 'category', 'image_path', 'is_available']),
            'categories' => Category::orderBy('name')->pluck('name'),
            'recommendations' => $this->recommendationService->getTopRecommendations(5),
            'pendingReview' => $this->resolvePendingReview($tableSessionId),
        ]);
    }

    /**
     * A customer is prompted to fill out the menu evaluation questionnaire
     * once their order in this table session is marked "selesai" (Completed)
     * — as long as something they bought still hasn't been answered.
     */
    private function resolvePendingReview(?string $tableSessionId): ?array
    {
        if (! $tableSessionId) {
            return null;
        }

        $order = Order::where('table_session_id', $tableSessionId)
            ->where('status', 'selesai')
            ->with('items', 'reviews')
            ->latest()
            ->get()
            ->first(function (Order $order) {
                $purchasedMenuIds = $order->items->pluck('menu_id')->unique();
                $reviewedMenuIds = $order->reviews->pluck('menu_id')->unique();

                return $purchasedMenuIds->diff($reviewedMenuIds)->isNotEmpty();
            });

        return $order ? ['order_id' => $order->id] : null;
    }
}
