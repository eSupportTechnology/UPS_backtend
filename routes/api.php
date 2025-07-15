<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Super Admin routes
Route::middleware('superadmin')->prefix('superadmin')->group(function () {
});

// Admin routes
Route::middleware('admin')->prefix('admin')->group(function () {
});

// Operator routes
Route::middleware('operator')->prefix('operator')->group(function () {
});

// Technician routes
Route::middleware('technician')->prefix('technician')->group(function () {
});

// Customer routes
Route::middleware('customer')->prefix('customer')->group(function () {
});
