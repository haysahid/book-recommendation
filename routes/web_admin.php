<?php

use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\TransactionItemController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.book.scraping');
    })->name('dashboard');
    Route::get('scraping', [BookController::class, 'scraping'])->name('book.scraping');
    Route::get('preprocessing', [BookController::class, 'preprocessing'])->name('book.preprocessing');
    Route::resource('book', BookController::class);
    Route::resource('category', CategoryController::class);

    Route::get('book-export', [BookController::class, 'export'])->name('book.export');
    Route::get('transaction-item-export', [TransactionItemController::class, 'export'])->name('transaction-item.export');
    Route::get('user-export', [UserController::class, 'export'])->name('user.export');

    // User
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::post('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Order
    Route::get('/order', [OrderController::class, 'index'])->name('order');
    Route::get('/order/create', [OrderController::class, 'create'])->name('order.create');
    Route::get('/order/{invoice}', [OrderController::class, 'edit'])->name('order.edit');
});
