<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OfflineTransactionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerDashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('store.home');
Route::get('/produk', fn () => redirect()->to(route('store.home').'#produk'))->name('store.products');
Route::get('/produk/{product}', [StorefrontController::class, 'show'])->name('store.product');

Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/keranjang/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::middleware(['auth', 'role:buyer'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/akun', [BuyerDashboardController::class, 'index'])->name('buyer.dashboard');
    Route::put('/akun/profil', [BuyerDashboardController::class, 'update'])->name('buyer.update');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('produk', AdminProductController::class)->except(['show'])->parameters(['produk' => 'product']);
    Route::get('/stok', [StockController::class, 'index'])->name('stock.index');
    Route::patch('/stok/{product}', [StockController::class, 'update'])->name('stock.update');
    Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::patch('/pesanan/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::get('/transaksi-offline', [OfflineTransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transaksi-offline', [OfflineTransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/transaksi-offline/{transaction}', [OfflineTransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
});