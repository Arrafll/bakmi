<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\QrCodeService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate limiter for QR entry: 30 scans/min per IP.
        // Prevents token brute-force enumeration while allowing normal usage
        // (a table with 4 guests scanning near-simultaneously stays well under).
        RateLimiter::for('qr-scan', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Stricter limiter for cart mutations to prevent cart-spam
        RateLimiter::for('cart', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
