<?php

use App\Http\Controllers\Admin\API\BookController;
use App\Http\Controllers\Admin\API\CategoryController;
use App\Http\Controllers\Admin\API\OrderController;
use App\Http\Controllers\Admin\API\InvoiceController;
use App\Http\Controllers\Admin\API\RecommendationSystemController;
use App\Http\Controllers\Admin\API\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('api.admin.')->middleware(['auth:sanctum'])->group(function () {
    // Scraping Data
    Route::post('/categories', [CategoryController::class, 'storeBulk'])->name('categories.storeBulk');
    Route::post('/books', [BookController::class, 'storeBulk'])->name('books.storeBulk');

    // Pre-processing Data
    Route::get('/book', [BookController::class, 'index'])->name('book.index');
    Route::post('/books/clean-titles', [BookController::class, 'cleanTitleBulk'])->name('books.cleanTitleBulk');
    Route::post('/books/stem-titles', [BookController::class, 'stemTitleBulk'])->name('books.stemTitleBulk');

    // Order
    Route::post('checkout', [OrderController::class, 'checkoutStore'])->name('checkout');
    Route::put('change-order-status', [OrderController::class, 'changeStatus'])->name('order.change-status');

    // Invoice
    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');

    // Recommendation System (grouped with prefix)
    Route::prefix('recommendation-system')->name('recommendation-system.')->group(function () {
        Route::post('/train', [RecommendationSystemController::class, 'trainModel'])->name('train');
        Route::post('/tune', [RecommendationSystemController::class, 'tuneModel'])->name('tune');
        Route::get('/model-history', [RecommendationSystemController::class, 'modelHistory'])->name('model-history');
        Route::get('/active-model', [RecommendationSystemController::class, 'activeModel'])->name('active-model');
        Route::post('/activate-model/{modelId}', [RecommendationSystemController::class, 'activateModel'])->name('activate-model');
        Route::delete('/model-history/{modelId}', [RecommendationSystemController::class, 'deleteModel'])->name('delete-model');
        Route::get('/recommend/{userId}', [RecommendationSystemController::class, 'recommend'])->name('recommend');
    });

    // Book
    Route::get('/recommended-books/{userId}', [BookController::class, 'userRecommendedBooks'])->name('user-recommended-books');

    // Setting
    Route::post('/setting/auto-train-model', [SettingController::class, 'setAutoTrainingModel'])->name('setting.auto-train-model');
});
