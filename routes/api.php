<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TransportController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;
// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public data routes
Route::get('/services/categories', [ServiceController::class, 'index']);
Route::get('/services/providers/{categoryId}', [ServiceController::class, 'providers']);
Route::get('/services/provider/{id}', [ServiceController::class, 'show']);

Route::get('/transport', [TransportController::class, 'index']);
Route::get('/transport/search', [TransportController::class, 'search']);
Route::get('/transport/{id}', [TransportController::class, 'show']);

Route::get('/products/categories', [ProductController::class, 'index']);
Route::get('/products/category/{categoryId}', [ProductController::class, 'products']);
Route::get('/products/compare/{productId}', [ProductController::class, 'compare']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/promotions', [ProductController::class, 'promotions']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('documents', DocumentController::class);

    Route::post('/services/provider/{providerId}/review', [ServiceController::class, 'storeReview']);

    Route::get('/transactions/summary', [TransactionController::class, 'summary']);
    Route::apiResource('transactions', TransactionController::class);

    Route::apiResource('budgets', BudgetController::class)->only(['index', 'store', 'show']);

    Route::get('/chat', [ChatController::class, 'index']);
    Route::post('/chat', [ChatController::class, 'store']);
    Route::post('/chat/ai', [ChatController::class, 'ai']);
});
