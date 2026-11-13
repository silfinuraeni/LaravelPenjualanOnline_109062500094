<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route untuk Guest (Belum Login: Login & Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

// Route untuk Logout (Memerlukan Auth)
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Route Katalog Produk / Shop (Publik)
Route::get('/', [ProductCatalogController::class, 'index'])->name('shop.index'); 
Route::get('/shop', [ProductCatalogController::class, 'index'])->name('shop.catalog'); 
Route::get('/produk/{product:slug}', [ProductCatalogController::class, 'show'])->name('shop.show'); 

// Route untuk Customer (Keranjang, Checkout, & Pesanan Saya)
Route::middleware(['auth','role:customer'])->group(function () { 
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index'); 
    Route::post('/keranjang/{product}', [CartController::class, 'store'])->name('cart.store'); 
    Route::patch('/keranjang/{product}', [CartController::class, 'update'])->name('cart.update'); 
    Route::delete('/keranjang/{product}', [CartController::class, 'destroy'])->name('cart.destroy'); 
    
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index'); 
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store'); 
    
    Route::get('/pesanan', [CustomerOrderController::class, 'index'])->name('customer.orders.index'); 
    Route::get('/pesanan/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show'); 
}); 

// Route untuk Admin (Dashboard, Manajemen Order, Kategori, & Produk)
Route::prefix('admin')->name('admin.')->middleware(['auth','role:admin'])->group(function () { 
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard'); 
    
    // Manajemen Order Admin
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index'); 
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show'); 
    Route::patch('/orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('orders.confirm'); 
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status'); 
    Route::patch('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel'); 

    // Manajemen Kategori & Produk Admin
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::resource('products', AdminProductController::class)->except('show');
});