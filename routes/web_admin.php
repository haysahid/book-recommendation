<?php

use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.book.scraping');
    })->name('dashboard');
    Route::get('scraping', [BookController::class, 'scraping'])->name('book.scraping');
    Route::get('preprocessing', [BookController::class, 'preprocessing'])->name('book.preprocessing');
    Route::resource('book', BookController::class);
    Route::resource('category', CategoryController::class);
});
