<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VoucherController;

Route::get('/', function () {
    return redirect()->route('menu.index');
});

Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

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
