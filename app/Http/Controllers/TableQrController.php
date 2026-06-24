<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Menu;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TableQrController extends Controller
{
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
                    'branch'    => $table->branch,
                    'sessionId' => $request->session()->get('table.session_id'),
                ],
                'menus'      => Menu::orderBy('category')
                    ->orderBy('name')
                    ->get(['id', 'name', 'description', 'price', 'category', 'image_path', 'is_available']),
                'categories' => Category::orderBy('name')->pluck('name'),
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
            'branch'     => $table->branch,
            'session_id' => $tableSessionId,
        ]);

        // Delete the old cart now that the new session is established.
        if ($oldSessionId) {
            CartItem::where('session_id', $oldSessionId)->delete();
        }

        return Inertia::render('Order/Index', [
            'table' => [
                'name'      => $table->name,
                'branch'    => $table->branch,
                'sessionId' => $tableSessionId,
            ],
            'menus'      => Menu::orderBy('category')
                ->orderBy('name')
                ->get(['id', 'name', 'description', 'price', 'category', 'image_path', 'is_available']),
            'categories' => Category::orderBy('name')->pluck('name'),
        ]);
    }
}
