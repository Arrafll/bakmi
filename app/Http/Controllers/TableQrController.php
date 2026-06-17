<?php

namespace App\Http\Controllers;

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

        // Validate token format before hitting the DB (prevents needless queries
        // from random/malformed tokens; token is alphanumeric, 32-64 chars)
        abort_unless(
            preg_match('/^[A-Za-z0-9]{32,64}$/', $qrToken) === 1,
            404
        );

        // Find table or 404 – intentionally no "invalid token" message to
        // prevent oracle attacks that distinguish "wrong format" vs "not found"
        $table = RestaurantTable::where('qr_token', $qrToken)
            ->where('is_active', true)
            ->firstOrFail();

        // ── Session hardening ─────────────────────────────────────────────────
        // Regenerate the session ID to prevent session fixation.
        // This is called BEFORE we write anything to the session so the data
        // lands in the fresh session, not the old one.
        $request->session()->regenerate();

        // A table_session_id groups every cart action and order placed in this
        // scan session.  Useful for: grouping orders per visit, analytics,
        // and allowing staff to see "all orders from Table 3 tonight".
        $tableSessionId = (string) Str::uuid();

        $request->session()->put('table', [
            'id'         => $table->id,
            'name'       => $table->name,
            'branch'     => $table->branch,
            'session_id' => $tableSessionId,
        ]);

        // Clear any stale cart from a previous session on this device
        $request->session()->forget('cart');

        // ── Render ────────────────────────────────────────────────────────────
        // IMPORTANT: table.id is intentionally NOT sent to the frontend.
        // All order operations look it up via session('table.id') on the server.
        return Inertia::render('Order/Index', [
            'table' => [
                'name'       => $table->name,
                'branch'     => $table->branch,
                'sessionId'  => $tableSessionId,
            ],
            'menus'      => Menu::orderBy('category')
                ->orderBy('name')
                ->get(['id', 'name', 'description', 'price', 'category', 'image', 'is_available']),
            'categories' => Category::orderBy('name')->pluck('name'),
        ]);
    }
}
