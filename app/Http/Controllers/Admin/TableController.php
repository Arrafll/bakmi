<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TableController extends Controller
{
    private QrCodeService $qrService;

    public function __construct(QrCodeService $qrService)
    {
        $this->qrService = $qrService;
    }

    // ── List ─────────────────────────────────────────────────────────────────

    public function index(): \Inertia\Response
    {
        $tables = RestaurantTable::withCount('orders')
            ->orderBy('name')
            ->paginate(10)
            ->through(fn(RestaurantTable $t) => [
                'id'          => $t->id,
                'name'        => $t->name,
                'is_active'   => $t->is_active,
                'qr_code_url' => $t->qrCodeExists() ? $t->qr_code_url : null,
                'qr_url'      => $t->qr_url,
                'orders_count' => $t->orders_count,
            ]);

        return Inertia::render('Admin/Tables', ['tables' => $tables]);
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:100'],
        ]);

        $table = RestaurantTable::create([
            'name'     => $data['name'],
            'qr_token' => Str::random(40),
        ]);

        // Pre-generate QR code immediately
        $this->qrService->generate($table);

        return back()->with('success', "Table \"{$table->name}\" created.");
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, RestaurantTable $table): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['sometimes', 'required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $table->update($data);

        return back()->with('success', 'Table updated.');
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function destroy(RestaurantTable $table): \Illuminate\Http\RedirectResponse
    {
        if ($table->qrCodeExists()) {
            @unlink($table->qr_code_path);
        }

        $table->delete();

        return back()->with('success', "Table \"{$table->name}\" deleted.");
    }

    // ── Regenerate QR token ──────────────────────────────────────────────────

    public function regenerateQr(RestaurantTable $table): \Illuminate\Http\RedirectResponse
    {
        $table->regenerateToken(); // deletes old SVG, issues new token
        $this->qrService->generate($table);

        return back()->with('success', "QR code regenerated for \"{$table->name}\". Old QR codes are now invalid.");
    }

    // ── Download SVG / inline SVG for print ─────────────────────────────────

    public function downloadQr(RestaurantTable $table): BinaryFileResponse
    {
        // Generate on-the-fly if missing (e.g. after server move)
        if (! $table->qrCodeExists()) {
            $this->qrService->generate($table);
        }

        $filename = Str::slug($table->name) . '-qr.svg';

        return response()->download($table->qr_code_path, $filename, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }

    // ── Inline SVG (for print page) ──────────────────────────────────────────

    public function inlineSvg(RestaurantTable $table): \Illuminate\Http\Response
    {
        $svg = $this->qrService->generateInline($table);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}
