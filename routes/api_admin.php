<?php

use App\Http\Controllers\Admin\API\BookController;
use App\Http\Controllers\Admin\API\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('api.admin.')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/categories', [CategoryController::class, 'storeBulk'])->name('categories.storeBulk');
    Route::post('/books', [BookController::class, 'storeBulk'])->name('books.storeBulk');

    Route::get('/book', [BookController::class, 'index'])->name('book.index');
    Route::post('/books/clean-titles', [BookController::class, 'cleanTitleBulk'])->name('books.cleanTitleBulk');
    Route::post('/books/stem-titles', [BookController::class, 'stemTitleBulk'])->name('books.stemTitleBulk');
});
