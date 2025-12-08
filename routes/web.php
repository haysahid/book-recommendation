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

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'store'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

require __DIR__ . '/web_admin.php';
