<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [BookController::class, 'index'])->name('book.index');
Route::get('/book', [BookController::class, 'index'])->name('book.index');
Route::get('/book/{bookSlug}', [BookController::class, 'show'])->name('book.show');
Route::get('/cart', [OrderController::class, 'cart'])->name('cart.index');
Route::get('/favorite', [BookController::class, 'favorite'])->name('favorite.index');

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'store'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/checkout-guest', [OrderController::class, 'checkoutGuest'])->name('checkout.guest');
Route::get('/order-success-guest', [OrderController::class, 'orderSuccessGuest'])->name('order.success.guest');
Route::get('/my-order-guest/{invoice_code}', [OrderController::class, 'myOrderDetail'])->name('my-order.detail.guest');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::get('/order-success', [OrderController::class, 'orderSuccess'])->name('order.success');
    Route::get('/my-order', [OrderController::class, 'myOrder'])->name('my-order');
    Route::get('/my-order/{invoice_code}', [OrderController::class, 'myOrderDetail'])->name('my-order.detail');
});

require __DIR__ . '/web_admin.php';
