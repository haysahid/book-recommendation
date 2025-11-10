<?php

use App\Http\Controllers\Admin\BookController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('book', BookController::class);
});
