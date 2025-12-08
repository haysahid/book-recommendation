<?php

use App\Http\Controllers\API\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('/sync-cart', [OrderController::class, 'syncCart'])->name('api.sync-cart');

require __DIR__ . '/api_admin.php';
