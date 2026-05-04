<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilialController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

// Просмотр данных (для витрины сайта)
Route::get('filials', [FilialController::class, 'index']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::get('services', [ServiceController::class, 'index']);

// Оформление заказа клиентом
Route::post('orders', [OrderController::class, 'store']);

Route::middleware('auth:api')->group(function () {

    // Пользователи и профиль
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users', [AuthController::class, 'index']);
    Route::post('register', [AuthController::class, 'register']);

    // Управление складом
    Route::put('products/{id}/stock', [ProductController::class, 'updateStock']);

    // Товары
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    // Категории
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Услуги
    Route::post('services', [ServiceController::class, 'store']);
    Route::put('services/{id}', [ServiceController::class, 'update']);
    Route::delete('services/{id}', [ServiceController::class, 'destroy']);
    Route::put('services/{id}/toggle-active', [ServiceController::class, 'toggleActive']);

    // Заказы
    Route::get('orders', [OrderController::class, 'index']);
    Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Филиалы
    Route::post('filials', [FilialController::class, 'store']);
    Route::delete('filials/{id}', [FilialController::class, 'destroy']);
});