<?php

use App\Http\Controllers\Admin\API\BookController;
use App\Http\Controllers\Admin\API\CategoryController;
use App\Http\Controllers\Admin\API\OrderController;
use App\Http\Controllers\Admin\API\InvoiceController;
use App\Http\Controllers\Admin\API\RecommendationSystemController;
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

    // Recommendation System
    Route::post('/recommendation-system/train', [RecommendationSystemController::class, 'trainModel'])->name('recommendation-system.train');
    Route::post('/recommendation-system/tune', [RecommendationSystemController::class, 'tuneModel'])->name('recommendation-system.tune');
    Route::get('/recommendation-system/model-history', [RecommendationSystemController::class, 'modelHistory'])->name('recommendation-system.model-history');
    Route::get('/recommendation-system/active-model', [RecommendationSystemController::class, 'activeModel'])->name('recommendation-system.active-model');
    Route::post('/recommendation-system/activate-model/{modelId}', [RecommendationSystemController::class, 'activateModel'])->name('recommendation-system.activate-model');
    Route::get('/recommendation-system/recommend/{userId}', [RecommendationSystemController::class, 'recommend'])->name('recommendation-system.recommend');
});
