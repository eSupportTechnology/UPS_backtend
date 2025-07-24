<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/create-user', [AuthController::class, 'createUserWithAutoPassword']);
Route::get('/all-users', [UserController::class, 'getAllUsers']);
Route::put('/update-users/{id}', [UserController::class, 'updateUser']);
Route::delete('/delete-users/{id}', [UserController::class, 'deleteUser']);

Route::post('/admin/users/activate/{id}', [UserController::class, 'activateUser']);
Route::post('/admin/users/deactivate/{id}', [UserController::class, 'deactivateUser']);

Route::post('/login', [AuthController::class, 'login']);

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
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
