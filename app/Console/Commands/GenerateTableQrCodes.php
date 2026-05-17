<?php

namespace App\Console\Commands;

use App\Models\RestaurantTable;
use App\Services\QrCodeService;
use Illuminate\Console\Command;

class GenerateTableQrCodes extends Command
{
    /**
     * php artisan tables:generate-qr
     * php artisan tables:generate-qr --force
     * php artisan tables:generate-qr --tables=1,3,5
     */
    protected $signature = 'tables:generate-qr
                            {--force           : Overwrite existing QR files}
                            {--tables=         : Comma-separated table IDs to process}';

    protected $description = 'Generate SVG QR code files for restaurant tables';

    public function handle(QrCodeService $service): int
    {
        $force  = (bool) $this->option('force');
        $ids    = $this->option('tables');
        $query  = RestaurantTable::where('is_active', true);

        if ($ids) {
            $query->whereIn('id', array_map('intval', explode(',', $ids)));
        }

        $tables = $query->get();

        if ($tables->isEmpty()) {
            $this->warn('No active tables found. Create tables first.');
            return self::FAILURE;
        }

        $this->info("Processing {$tables->count()} table(s)…");

        $bar       = $this->output->createProgressBar($tables->count());
        $generated = 0;
        $skipped   = 0;

        foreach ($tables as $table) {
            if (!$force && $table->qrCodeExists()) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $service->generate($table);
            $generated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Generated', $generated],
                ['Skipped (already exists)', $skipped],
            ]
        );

        $this->info('Done. QR files stored in storage/app/public/qrcodes/');
        $this->line('Run <comment>php artisan storage:link</comment> if not already linked.');

        return self::SUCCESS;
    }
}
