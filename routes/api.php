<?php

use App\Http\Controllers\AMCContractController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ShopInventoryController;
use App\Http\Controllers\TicketController;
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

    //Ticket
    Route::get('/all-tickets', [TicketController::class, 'getAllTickets']);
    Route::post('/assign-ticket', [TicketController::class, 'assignTicket']);

    //Technician Users
    Route::get('/all-technician-users', [UserController::class, 'getAllTechnicianUsers']);


});

// Admin routes
Route::middleware('admin')->prefix('admin')->group(function () {
});

// Operator routes
Route::middleware('operator')->prefix('operator')->group(function () {
});

// Technician routes
Route::middleware('technician')->group(function () {

    //Ticket
    Route::post('/accept-ticket', [TicketController::class, 'acceptTicket']);
    Route::post('/complete-ticket', [TicketController::class, 'completeTicket']);

});

// Customer routes
Route::middleware(['auth:sanctum', 'customer'])->group(function () {

    //Ticket
    Route::post('/create-ticket', [TicketController::class, 'createTicket']);
    Route::get('/tickets-customer/{customer_id}', [TicketController::class, 'getTicketsByCustomer']);

});
