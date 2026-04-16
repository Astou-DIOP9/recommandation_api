<?php

use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductViewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    Route::get('/products', [ProductsController::class, 'index']);
    Route::get('/products/{products}', [ProductsController::class, 'show']);

    Route::get('/product-views', [ProductViewsController::class, 'index']);
    Route::post('/product-views', [ProductViewsController::class, 'store']);
    Route::get('/product-views/{product_views}', [ProductViewsController::class, 'show']);
    Route::delete('/product-views/{product_views}', [ProductViewsController::class, 'destroy']);

    Route::get('/recommendations/products/{productId}', [ProductViewsController::class, 'recommendations']);
});
