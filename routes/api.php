<?php

use App\Http\Controllers\AMCContractController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ShopInventoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

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
Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
    //Users
    Route::post('/create-user', [AuthController::class, 'createUserWithAutoPassword']);
    Route::get('/all-users', [UserController::class, 'getAllUsers']);
    Route::put('/update-users/{id}', [UserController::class, 'updateUser']);
    Route::delete('/delete-users/{id}', [UserController::class, 'deleteUser']);

    Route::post('/users-activate/{id}', [UserController::class, 'activateUser']);
    Route::post('/users-deactivate/{id}', [UserController::class, 'deactivateUser']);
    Route::get('/active-customers', [UserController::class, 'getActiveCustomers']);

    //ShopInventory
    Route::get('/all-shopInventories', [ShopInventoryController::class, 'getAllShopInventories']);
    Route::post('/create-shopInventories', [ShopInventoryController::class, 'createShopInventory']);
    Route::put('/update-shopInventories/{id}', [ShopInventoryController::class, 'updateShopInventories']);
    Route::delete('/delete-shopInventories/{id}', [ShopInventoryController::class, 'deleteShopInventories']);

    // Branches
    Route::get('/all-branches', [BranchController::class, 'getAllBranches']);
    Route::post('/create-branch', [BranchController::class, 'createBranch']);
    Route::put('/update-branch/{id}', [BranchController::class, 'updateBranch']);
    Route::delete('/delete-branch/{id}', [BranchController::class, 'deleteBranch']);

    Route::post('/admin/branches/activate/{id}', [BranchController::class, 'activateBranch']);
    Route::post('/admin/branches/deactivate/{id}', [BranchController::class, 'deactivateBranch']);
    Route::get('/active-branches', [BranchController::class, 'getActiveBranches']);

    //AMCContract
    Route::post('/create-contract', [AMCContractController::class, 'createContract']);
    Route::get('/all-contract', [AMCContractController::class, 'getAllContract']);
    Route::put('/update-amc-contract/{id}', [AMCContractController::class, 'updateAMCContract']);
    Route::delete('/delete-amc-contract/{id}', [AMCContractController::class, 'deleteAMCContract']);

    Route::post('/amc-contracts-activate/{id}', [AMCContractController::class, 'activateAMCContract']);
    Route::post('/amc-contracts-deactivate/{id}', [AMCContractController::class, 'deactivateAMCContract']);
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
