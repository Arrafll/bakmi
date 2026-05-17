<?php

use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableQrController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('menu.index');
});

Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

// ── QR Table Entry ────────────────────────────────────────────────────────────
// Rate-limited to 30/min per IP to prevent token enumeration.
// Token validated + session regenerated inside the controller.
// Table ID is NEVER exposed in the URL – only stored server-side in session.
Route::get('/order/{qr_token}', [TableQrController::class, 'enter'])
    ->name('order.enter')
    ->middleware(['throttle:qr-scan'])
    ->where('qr_token', '[A-Za-z0-9]{32,64}'); // route-level pre-validation

// ── Cart routes ───────────────────────────────────────────────────────────────
// table.session middleware: ensures a QR scan established a table context first.
Route::middleware(['table.session', 'throttle:cart'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});

// ── Order routes ──────────────────────────────────────────────────────────────
Route::post('/order', [OrderController::class, 'store'])
    ->name('order.store')
    ->middleware('table.session');

Route::get('/order/{id}/success', [OrderController::class, 'success'])
    ->name('order.success')
    ->where('id', '[0-9]+'); // only numeric IDs – prevents clash with qr_token route

// Voucher apply (customer-facing)
Route::post('/voucher/apply', [VoucherController::class, 'apply'])
    ->name('voucher.apply')
    ->middleware('table.session');

// ── Admin auth routes (guests only) ──────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/admin', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');
});

// ── Admin protected routes ────────────────────────────────────────────────────
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

    // ── Table / QR management ─────────────────────────────────────────────────
    Route::prefix('tables')->name('tables.')->group(function () {
        Route::get('/', [TableController::class, 'index'])->name('index');
        Route::post('/', [TableController::class, 'store'])->name('store');
        Route::put('/{table}', [TableController::class, 'update'])->name('update');
        Route::delete('/{table}', [TableController::class, 'destroy'])->name('destroy');

        // Bonus: regenerate QR token + re-issue file
        Route::post('/{table}/regenerate-qr', [TableController::class, 'regenerateQr'])->name('regenerate-qr');
        // Bonus: download SVG for printing
        Route::get('/{table}/download-qr', [TableController::class, 'downloadQr'])->name('download-qr');
        // Bonus: inline SVG (for print-page embed)
        Route::get('/{table}/inline-svg', [TableController::class, 'inlineSvg'])->name('inline-svg');
    });
});
