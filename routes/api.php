<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class)->only(['index'])
    ->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);
});

Route::post('/login', function () {
    return response()->json(['message' => 'Unauthorized'], 401);
})->name('login');