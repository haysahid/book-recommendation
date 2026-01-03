<?php

use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\OrderController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('/sync-cart', [OrderController::class, 'syncCart'])->name('sync-cart');
    Route::get('/destinations', [OrderController::class, 'destinations'])->name('destinations');
    Route::get('/shipping-cost', [OrderController::class, 'shippingCost'])->name('shipping-cost');
    Route::post('/checkout-guest', [OrderController::class, 'checkoutGuest'])->name('checkout-guest');
    Route::get('/check-payment-guest', [OrderController::class, 'checkPayment'])->name('check-payment-guest');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/voucher', [OrderController::class, 'getVouchers'])->name('voucher');
        Route::post('/check-voucher', [OrderController::class, 'checkVoucher'])->name('check-voucher');

        Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/cancel-order', [OrderController::class, 'cancelOrder'])->name('cancel-order');

        Route::get('/check-payment', [OrderController::class, 'checkPayment'])->name('check-payment');
        Route::put('/change-payment-type', [OrderController::class, 'changePaymentType'])->name('change-payment-type');
        Route::post('/confirm-payment', [OrderController::class, 'confirmPayment'])->name('confirm-payment');

        Route::get('/recommended-books', [BookController::class, 'userRecommendedBooks'])->name('recommended-books');

        Route::post('/book/add-review/{transactionItemId}', [BookController::class, 'addBookReview'])->name('book.add-review');
    });
});

require __DIR__ . '/api_admin.php';
