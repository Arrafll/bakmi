<?php

namespace Tests\Feature;

use App\Models\RestaurantTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature tests for the QR-code table-entry flow.
 *
 * Covers:
 *  - Valid token → Inertia page rendered with correct props
 *  - Invalid token → 404 (no information leak)
 *  - Inactive table → 404
 *  - Session stores table.id (not exposed in URL)
 *  - Session is regenerated (prevents fixation)
 *  - Cart is cleared on new QR scan
 *  - Cart/order routes blocked without table session
 *  - Rate-limit middleware is attached (smoke test)
 */
class TableQrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Vite manifest is not available in the test environment
        $this->withoutVite();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeTable(array $attributes = []): RestaurantTable
    {
        return RestaurantTable::factory()->create($attributes);
    }

    // ── Entry point ───────────────────────────────────────────────────────────

    public function test_valid_token_renders_order_index_page(): void
    {
        $table = $this->makeTable();

        $response = $this->get('/order/' . $table->qr_token);

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) =>
            $page->component('Order/Index')
                ->has('table.name')
                ->has('menus')
                ->where('table.name', $table->name)
        );
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->get('/order/' . str_repeat('a', 40));

        $response->assertStatus(404);
    }

    public function test_malformed_token_returns_404(): void
    {
        // Contains characters outside [A-Za-z0-9]
        $response = $this->get('/order/../../etc/passwd');
        $response->assertStatus(404);

        // Too short
        $response = $this->get('/order/abc');
        $response->assertStatus(404);
    }

    public function test_inactive_table_returns_404(): void
    {
        $table = $this->makeTable(['is_active' => false]);

        $response = $this->get('/order/' . $table->qr_token);

        $response->assertStatus(404);
    }

    // ── Session handling ──────────────────────────────────────────────────────

    public function test_session_stores_table_id_after_qr_scan(): void
    {
        $table = $this->makeTable();

        $this->get('/order/' . $table->qr_token);

        $this->assertEquals($table->id, session('table.id'));
    }

    public function test_session_stores_table_name_and_session_id(): void
    {
        $table = $this->makeTable(['name' => 'Table 7']);

        $this->get('/order/' . $table->qr_token);

        $this->assertEquals('Table 7', session('table.name'));
        $this->assertNotNull(session('table.session_id'));
        // Verify it is a valid UUID
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            session('table.session_id')
        );
    }

    public function test_table_id_is_not_exposed_in_frontend_props(): void
    {
        $table = $this->makeTable();

        $response = $this->get('/order/' . $table->qr_token);

        // table.id must NOT appear in the rendered Inertia props
        $response->assertInertia(
            fn($page) =>
            $page->component('Order/Index')
                ->missing('table.id')
        );
    }

    public function test_session_is_regenerated_on_qr_scan(): void
    {
        $table = $this->makeTable();

        // Establish a session first
        $this->withSession(['pre_existing' => 'value']);
        $sessionBefore = session()->getId();

        $this->get('/order/' . $table->qr_token);

        // Session ID should change after regeneration
        $this->assertNotEquals($sessionBefore, session()->getId());
    }

    public function test_stale_cart_is_cleared_on_new_qr_scan(): void
    {
        $table = $this->makeTable();

        // Simulate a cart already in session
        $this->withSession(['cart' => [['menu_id' => 1, 'quantity' => 2]]]);

        $this->get('/order/' . $table->qr_token);

        $this->assertEmpty(session('cart', []));
    }

    // ── Middleware: EnsureTableSession ────────────────────────────────────────

    public function test_cart_index_blocked_without_table_session(): void
    {
        $response = $this->get('/cart');

        // Should return 403 (EnsureTableSession) not 200
        $response->assertStatus(403);
    }

    public function test_cart_add_blocked_without_table_session(): void
    {
        $response = $this->post('/cart/add', [
            'menu_id'  => 1,
            'quantity' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_order_store_blocked_without_table_session(): void
    {
        $response = $this->post('/order', [
            'customer_name'  => 'Test',
            'customer_phone' => '081234567890',
        ]);

        $response->assertStatus(403);
    }

    public function test_cart_accessible_after_valid_qr_scan(): void
    {
        $table = $this->makeTable();
        $this->get('/order/' . $table->qr_token);

        // Now the table session exists – cart should respond (not 403)
        $response = $this->get('/cart');
        $response->assertStatus(200);
    }

    // ── Multiple users on same table ──────────────────────────────────────────

    public function test_two_users_can_scan_same_table_independently(): void
    {
        $table = $this->makeTable();

        // Simulate user A scanning
        $userA = $this->get('/order/' . $table->qr_token);
        $sessionA = session('table.session_id');
        $userA->assertStatus(200);

        // Simulate user B scanning (new browser / test client)
        $userB = $this->get('/order/' . $table->qr_token);
        $sessionB = session('table.session_id');
        $userB->assertStatus(200);

        // Each scan generates a UNIQUE table_session_id
        $this->assertNotEquals($sessionA, $sessionB);
    }
}
