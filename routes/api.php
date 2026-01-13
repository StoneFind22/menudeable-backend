<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MenuBuilderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PublicMenuController;
use App\Http\Controllers\Api\RestaurantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/public/menu/{slug}', [PublicMenuController::class, 'show']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard-stats', [DashboardController::class, 'index']);

    // Gestión de Restaurante
    Route::post('/restaurants', [RestaurantController::class, 'store']);
    Route::put('/restaurants', [RestaurantController::class, 'update']);
    Route::get('/my-restaurant', [RestaurantController::class, 'show']);
    
    // Menu Builder (Carga completa optimizada)
    Route::get('/menu-builder', [MenuBuilderController::class, 'index']);

    // Gestión de Categorías
    Route::post('/categories/reorder', [CategoryController::class, 'reorder']);
    Route::apiResource('categories', CategoryController::class);

    // Gestión de Productos
    Route::apiResource('products', ProductController::class);
});
