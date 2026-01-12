<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RestaurantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Gestión de Restaurante
    Route::post('/restaurants', [RestaurantController::class, 'store']);
    Route::get('/my-restaurant', [RestaurantController::class, 'show']);

    // Gestión de Categorías
    Route::post('/categories/reorder', [CategoryController::class, 'reorder']);
    Route::apiResource('categories', CategoryController::class);

    // Gestión de Productos
    Route::apiResource('products', ProductController::class);
});
