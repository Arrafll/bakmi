<?php

namespace App\Services;

use App\Models\RestaurantTable;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    private const QR_SIZE        = 300;
    private const ERROR_LEVEL    = 'H'; // Highest error correction – survives partial damage/printing artifacts
    private const STORAGE_SUBDIR = 'qrcodes';

    /**
     * Generate (or re-generate) the SVG QR code for one table.
     *
     * The file is written to:  storage/app/public/qrcodes/{token}.svg
     * Accessible via:          /storage/qrcodes/{token}.svg
     *
     * @throws \RuntimeException if the directory cannot be created.
     */
    public function generate(RestaurantTable $table): string
    {
        $dir = $this->ensureStorageDirectory();

        $url  = url('/order/' . $table->qr_token);
        $path = $dir . DIRECTORY_SEPARATOR . $table->qr_token . '.svg';

        QrCode::format('svg')
            ->size(self::QR_SIZE)
            ->errorCorrection(self::ERROR_LEVEL)
            ->generate($url, $path);

        Log::info('QR code generated', [
            'table' => $table->name,
            'path'  => $path,
        ]);

        return $path;
    }

    /**
     * Generate QR codes for every active table.
     * Skips tables whose file already exists unless $force is true.
     *
     * @return array{generated: int, skipped: int}
     */
    public function generateAll(bool $force = false): array
    {
        $generated = 0;
        $skipped   = 0;

        RestaurantTable::where('is_active', true)->each(function (RestaurantTable $table) use ($force, &$generated, &$skipped) {
            if (!$force && $table->qrCodeExists()) {
                $skipped++;
                return;
            }

            $this->generate($table);
            $generated++;
        });

        return compact('generated', 'skipped');
    }

    /**
     * Return the SVG markup as a string (useful for inline embedding).
     */
    public function generateInline(RestaurantTable $table): string
    {
        return (string) QrCode::format('svg')
            ->size(self::QR_SIZE)
            ->errorCorrection(self::ERROR_LEVEL)
            ->generate(url('/order/' . $table->qr_token));
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function ensureStorageDirectory(): string
    {
        $dir = storage_path('app/public/' . self::STORAGE_SUBDIR);

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create QR storage directory: {$dir}");
        }

        return $dir;
    }
}
