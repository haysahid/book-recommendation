<?php

use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('book', BookController::class);
    Route::resource('category', CategoryController::class);
    Route::post('categories', [CategoryController::class, 'storeBulk'])->name('category.storeBulk');
});
