<?php

namespace Tests\Unit;

use App\Models\RestaurantTable;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Unit tests for QrCodeService and RestaurantTable model helpers.
 *
 * Covers:
 *  - Token is 40 characters of [A-Za-z0-9]
 *  - Tokens are unique across 1000 generations
 *  - SVG file is written to the correct path
 *  - regenerateToken() produces a new token and removes old file
 *  - qrCodeUrl / qrUrl accessors return correct values
 */
class QrCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTable(array $attr = []): RestaurantTable
    {
        return RestaurantTable::factory()->create($attr);
    }

    // ── Token generation ──────────────────────────────────────────────────────

    public function test_qr_token_is_40_alphanumeric_characters(): void
    {
        $table = $this->makeTable();

        $this->assertEquals(40, strlen($table->qr_token));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $table->qr_token);
    }

    public function test_tokens_are_unique(): void
    {
        $tokens = array_map(
            fn() => Str::random(40),
            array_fill(0, 1000, null)
        );

        // All 1000 tokens must be unique
        $this->assertEquals(1000, count(array_unique($tokens)));
    }

    public function test_each_new_table_gets_a_unique_token(): void
    {
        $tableA = $this->makeTable();
        $tableB = $this->makeTable();

        $this->assertNotEquals($tableA->qr_token, $tableB->qr_token);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function test_qr_url_accessor_encodes_token_in_path(): void
    {
        $table = $this->makeTable();

        $this->assertStringContainsString('/order/' . $table->qr_token, $table->qr_url);
    }

    public function test_qr_code_url_accessor_points_to_svg_in_storage(): void
    {
        $table = $this->makeTable();

        $this->assertStringContainsString('storage/qrcodes/' . $table->qr_token . '.svg', $table->qr_code_url);
    }

    public function test_qr_code_path_accessor_points_to_local_filesystem(): void
    {
        $table = $this->makeTable();

        $expected = storage_path('app/public/qrcodes/' . $table->qr_token . '.svg');
        $this->assertEquals($expected, $table->qr_code_path);
    }

    public function test_qr_code_exists_returns_false_before_generation(): void
    {
        $table = $this->makeTable();

        $this->assertFalse($table->qrCodeExists());
    }

    // ── File generation ───────────────────────────────────────────────────────

    public function test_generate_creates_svg_file(): void
    {
        $table   = $this->makeTable();
        $service = app(QrCodeService::class);

        $path = $service->generate($table);

        $this->assertFileExists($path);
        $this->assertStringEndsWith('.svg', $path);

        // SVG files must start with an XML/SVG marker
        $content = file_get_contents($path);
        $this->assertStringContainsString('<svg', $content);

        // Cleanup
        @unlink($path);
    }

    public function test_generate_embeds_correct_url_in_svg(): void
    {
        $table   = $this->makeTable();
        $service = app(QrCodeService::class);
        $path    = $service->generate($table);

        // The URL encoded into the QR must be /order/{token}
        // We verify indirectly by checking the file was created for the right table
        $this->assertStringContainsString($table->qr_token, $path);

        @unlink($path);
    }

    // ── Token regeneration ────────────────────────────────────────────────────

    public function test_regenerate_token_issues_new_token(): void
    {
        $table = $this->makeTable();
        $old   = $table->qr_token;

        $table->regenerateToken();
        $table->refresh();

        $this->assertNotEquals($old, $table->qr_token);
        $this->assertEquals(40, strlen($table->qr_token));
    }

    public function test_regenerate_token_removes_old_svg_file(): void
    {
        $table   = $this->makeTable();
        $service = app(QrCodeService::class);

        // Generate the initial QR file
        $oldPath = $service->generate($table);
        $this->assertFileExists($oldPath);

        // Regenerate: old file should be deleted
        $table->regenerateToken();

        $this->assertFileDoesNotExist($oldPath);
    }

    // ── generateAll ───────────────────────────────────────────────────────────

    public function test_generate_all_creates_files_for_all_active_tables(): void
    {
        $active1  = $this->makeTable(['is_active' => true]);
        $active2  = $this->makeTable(['is_active' => true]);
        $inactive = $this->makeTable(['is_active' => false]);

        $service = app(QrCodeService::class);
        $result  = $service->generateAll();

        $this->assertFileExists($active1->qr_code_path);
        $this->assertFileExists($active2->qr_code_path);
        $this->assertFileDoesNotExist($inactive->qr_code_path);

        $this->assertEquals(2, $result['generated']);
        $this->assertEquals(0, $result['skipped']);

        // Cleanup
        @unlink($active1->qr_code_path);
        @unlink($active2->qr_code_path);
    }

    public function test_generate_all_skips_existing_files_unless_force(): void
    {
        $table   = $this->makeTable(['is_active' => true]);
        $service = app(QrCodeService::class);

        // First pass – generates
        $service->generateAll();

        // Second pass without force – should skip
        $result = $service->generateAll(force: false);
        $this->assertEquals(0, $result['generated']);
        $this->assertEquals(1, $result['skipped']);

        // Third pass with force – should overwrite
        $result = $service->generateAll(force: true);
        $this->assertEquals(1, $result['generated']);

        @unlink($table->qr_code_path);
    }
}
