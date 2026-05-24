<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
// use App\Models\Menu; // Commenting out unused Menu import
use Inertia\Inertia;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VoucherController;

Route::get('/', function () {
    $company = [
        'name' => 'Bakmi Jawa Mas Agus',
        'tagline' => 'Sajian lezat pilihan kami',
        'description' => 'Bakmi Jawa Mas Agus adalah rumah makan keluarga yang menyajikan bakmi tradisional dengan resep turun-temurun. Kami menggunakan bahan segar dan teknik memasak yang menjaga cita rasa otentik.',
        'address' => 'Jl. Contoh No.123, Kota Contoh',
        'phone' => '0812-3456-7890',
        'opening_hours' => [
            'Senin-Jumat' => '09:00 - 21:00',
            'Sabtu-Minggu' => '09:00 - 22:00',
        ],
        'hero_image' => asset('images/hero.jpg'),
    ];
    return Inertia::render('Landing', [
        'company' => (object) $company,
    ]);
});

// ── QR Table Entry ────────────────────────────────────────────────────────────
// Rate-limited to 30/min per IP to prevent token enumeration.
// Token validated + session regenerated inside the controller.
// Table ID is NEVER exposed in the URL – only stored server-side in session.
Route::get('/order/{qr_token}', [TableQrController::class, 'enter'])
    ->name('order.enter')
    ->middleware(['throttle:qr-scan'])
    ->where('qr_token', '[A-Za-z0-9]{32,64}'); // route-level pre-validation

// Order routes
Route::post('/order', [OrderController::class, 'store'])->name('order.store');
Route::get('/order/{id}/success', [OrderController::class, 'success'])->name('order.success');

// Voucher apply (customer-facing)
Route::post('/voucher/apply', [VoucherController::class, 'apply'])->name('voucher.apply');

// Admin auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/admin', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');
});

// Admin protected routes
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // Master Menu
    Route::get('/menus', [AdminController::class, 'menusIndex'])->name('menus.index');
    Route::post('/menus', [AdminController::class, 'menusStore'])->name('menus.store');
    Route::put('/menus/{menu}', [AdminController::class, 'menusUpdate'])->name('menus.update');
    Route::delete('/menus/{menu}', [AdminController::class, 'menusDestroy'])->name('menus.destroy');

    // Voucher Master
    Route::get('/vouchers', [AdminController::class, 'vouchersIndex'])->name('vouchers.index');
    Route::post('/vouchers', [AdminController::class, 'vouchersStore'])->name('vouchers.store');
    Route::put('/vouchers/{voucher}', [AdminController::class, 'vouchersUpdate'])->name('vouchers.update');
    Route::delete('/vouchers/{voucher}', [AdminController::class, 'vouchersDestroy'])->name('vouchers.destroy');
});
