<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductViewsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'recommandation-api',
    ]);
});

Route::get('/health', function () {
    $dbStatus = 'ok';

    try {
        DB::select('SELECT 1');
    } catch (\Throwable $e) {
        $dbStatus = 'ko';
    }

    return response()->json([
        'status' => 'ok',
        'service' => 'recommandation-api',
        'db' => $dbStatus,
        'time' => now()->toIso8601String(),
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', function () {
    return response()->json([
        'message' => 'Methode non autorisee pour cette route.',
        'hint' => 'Utilisez POST /api/login avec email et password.',
    ], 405);
});
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth.token')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Utilisateurs (admin)
    Route::get('/users', [UserController::class, 'index']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::patch('/admin/users/{user}', [UserController::class, 'update']);
    Route::put('/admin/users/{user}/role', [UserController::class, 'update']);

    // Panier
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{item}', [CartController::class, 'update']);
    Route::delete('/cart/{item}', [CartController::class, 'destroy']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
    Route::delete('/cart', [CartController::class, 'clear']);
});

Route::get('/products', [ProductsController::class, 'index']);
Route::post('/products', [ProductsController::class, 'store']);
Route::get('/products/{product}', [ProductsController::class, 'show']);
Route::get('/products/{product}/reviews', [ReviewController::class, 'indexByProduct']);
Route::put('/products/{product}', [ProductsController::class, 'update']);
Route::patch('/products/{product}', [ProductsController::class, 'update']);
Route::delete('/products/{product}', [ProductsController::class, 'destroy']);

Route::get('/product-views', [ProductViewsController::class, 'index']);
Route::post('/product-views', [ProductViewsController::class, 'store']);
Route::get('/product-views/{productView}', [ProductViewsController::class, 'show']);
Route::put('/product-views/{productView}', [ProductViewsController::class, 'update']);
Route::patch('/product-views/{productView}', [ProductViewsController::class, 'update']);
Route::delete('/product-views/{productView}', [ProductViewsController::class, 'destroy']);

Route::get('/recommendations/products/{productId}', [ProductViewsController::class, 'recommendations']);
Route::get('/recommendations', [ProductViewsController::class, 'globalRecommendations']);

Route::middleware('auth.token')->group(function () {
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // Commandes: utilisateur proprietaire uniquement, sauf admin
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::patch('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
});

Route::get('/order-items', [OrderItemsController::class, 'index']);
Route::post('/order-items', [OrderItemsController::class, 'store']);
Route::get('/order-items/{orderItem}', [OrderItemsController::class, 'show']);
Route::put('/order-items/{orderItem}', [OrderItemsController::class, 'update']);
Route::patch('/order-items/{orderItem}', [OrderItemsController::class, 'update']);
Route::delete('/order-items/{orderItem}', [OrderItemsController::class, 'destroy']);
