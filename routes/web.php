<?php

use App\Http\Controllers\Admin\CriteriaController;
use App\Http\Controllers\Admin\MenuScoreController;
use App\Http\Controllers\Admin\RecommendationController;
use App\Http\Controllers\Admin\ReviewSubmissionController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MenuReviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
// use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\TableQrController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $company = [
        'name' => 'Bakmi Jawa Mas Agus',
        'tagline' => 'Masakan Tulus, Rasane Nyess Tenan',
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
})->name('home');

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

// Post-order menu evaluation questionnaire
Route::get('/orders/{order}/review', [MenuReviewController::class, 'create'])
    ->name('orders.review.show');
Route::post('/orders/{order}/review', [MenuReviewController::class, 'store'])
    ->name('orders.review.store');

// Voucher apply (customer-facing)
Route::post('/voucher/apply', [VoucherController::class, 'apply'])
    ->name('voucher.apply')
    ->middleware('table.session');

// ── SPK Recommendation (customer-facing, public) ──────────────────────────
Route::get('/rekomendasi', [RecommendationController::class, 'index'])
    ->name('recommendation.index')
    ->middleware('table.session');

// ── Admin auth routes (guests only) ──────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/admin', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');
});

// ── Admin protected routes ────────────────────────────────────────────────────
Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // Orders
    Route::get('/orders', [AdminController::class, 'ordersIndex'])->name('orders.index');
    Route::put('/orders/{order}/status', [AdminController::class, 'ordersUpdateStatus'])->name('orders.update-status');

    // Master Category
    Route::get('/categories', [AdminController::class, 'categoriesIndex'])->name('categories.index');
    Route::post('/categories', [AdminController::class, 'categoriesStore'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'categoriesUpdate'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'categoriesDestroy'])->name('categories.destroy');

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

    // ── Menu recommendation engine ────────────────────────────────────────────
    Route::prefix('criteria')->name('criteria.')->group(function () {
        Route::get('/', [CriteriaController::class, 'index'])->name('index');
        Route::post('/', [CriteriaController::class, 'store'])->name('store');
        Route::put('/{criterion}', [CriteriaController::class, 'update'])->name('update');
        Route::delete('/{criterion}', [CriteriaController::class, 'destroy'])->name('destroy');
    });

    Route::get('/menu-scores', [MenuScoreController::class, 'index'])->name('menu-scores.index');

    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');

    Route::get('/review-submissions', [ReviewSubmissionController::class, 'index'])->name('review-submissions.index');

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
