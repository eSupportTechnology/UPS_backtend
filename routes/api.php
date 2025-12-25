<?php

use App\Http\Controllers\AMCContractController;
use App\Http\Controllers\AMCMaintenanceController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryItemUsageController;
use App\Http\Controllers\ShopInventoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TrackController;
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
//Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
    Route::get('/dashboard/super-admin', [DashboardController::class, 'superAdminDashboard']);

    //Users
    Route::post('/create-user', [AuthController::class, 'createUserWithAutoPassword']);
    Route::get('/all-users', [UserController::class, 'getAllUsers']);
    Route::put('/update-users/{id}', [UserController::class, 'updateUser']);
    Route::delete('/delete-users/{id}', [UserController::class, 'deleteUser']);

    Route::post('/users-activate/{id}', [UserController::class, 'activateUser']);
    Route::post('/users-deactivate/{id}', [UserController::class, 'deactivateUser']);
    Route::get('/active-customers', [UserController::class, 'getActiveCustomers']);

    //Technicians
    Route::post('/create-technician', [UserController::class, 'createTechnician']);
    Route::get('/all-technician-users', [UserController::class, 'getAllTechnicianUsers']);
    Route::get('/technician/{id}', [UserController::class, 'getTechnician']);
    Route::put('/technician/{id}', [UserController::class, 'updateTechnician']);
    Route::delete('/technician/{id}', [UserController::class, 'deleteTechnician']);

    //Customers
    Route::post('/create-customer', [UserController::class, 'createCustomer']);
    Route::post('/customer/{customerId}/add-branches', [UserController::class, 'addCompanyCustomerBranches']);
    Route::put('/customer/{customerId}/update-branch/{branchId}', [UserController::class, 'updateCompanyCustomerBranch']);
    Route::delete('/customer/{customerId}/remove-branch/{branchId}', [UserController::class, 'removeCompanyCustomerBranch']);
    Route::get('/customer/{customerId}/branches', [UserController::class, 'getCompanyCustomerBranches']);

    //ShopInventory
    Route::get('/all-shopInventories', [ShopInventoryController::class, 'getAllShopInventories']);
    Route::post('/create-shopInventories', [ShopInventoryController::class, 'createShopInventory']);
    Route::put('/update-shopInventories/{id}', [ShopInventoryController::class, 'updateShopInventories']);
    Route::delete('/delete-shopInventories/{id}', [ShopInventoryController::class, 'deleteShopInventories']);

    Route::get('/shop-inventories/export/excel', [ShopInventoryController::class, 'exportExcel']);
    Route::get('/shop-inventories/export/pdf', [ShopInventoryController::class, 'exportPdf']);
    Route::get('/shop-inventories/report', [ShopInventoryController::class, 'generateReport']);

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

    Route::get('/amc-contracts/export/excel', [AMCContractController::class, 'exportExcel']);
    Route::get('/amc-contracts/export/pdf', [AMCContractController::class, 'exportPdf']);
    Route::get('/amc-contracts/report', [AMCContractController::class, 'generateReport']);

    Route::post('/amc-contracts-activate/{id}', [AMCContractController::class, 'activateAMCContract']);
    Route::post('/amc-contracts-deactivate/{id}', [AMCContractController::class, 'deactivateAMCContract']);

    //Ticket
    Route::get('/all-tickets', [TicketController::class, 'getAllTickets']);
    Route::post('/assign-ticket', [TicketController::class, 'assignTicket']);

    Route::get('/tickets/export/excel', [TicketController::class, 'exportExcel']);
    Route::get('/tickets/export/pdf', [TicketController::class, 'exportPdf']);
    Route::get('/tickets/report', [TicketController::class, 'generateReport']);

    // Inside Job Management
    Route::post('/inside-jobs/create-direct', [TicketController::class, 'createInsideJobDirect']);
    Route::post('/tickets/convert-to-inside-job', [TicketController::class, 'convertToInsideJob']);
    Route::get('/inside-jobs', [TicketController::class, 'getInsideJobs']);
    Route::get('/inside-jobs/{ticket_id}/job-card', [TicketController::class, 'getJobCard']);
    Route::post('/inside-jobs/inspect', [TicketController::class, 'inspectInsideJob']);
    Route::post('/inside-jobs/create-quote', [TicketController::class, 'createQuote']);
    Route::post('/inside-jobs/approve-quote', [TicketController::class, 'approveQuote']);
    Route::post('/inside-jobs/start-repair', [TicketController::class, 'startRepair']);
    Route::post('/inside-jobs/complete', [TicketController::class, 'completeInsideJob']);

    //Technician Users
    Route::get('/all-technician-users', [UserController::class, 'getAllTechnicianUsers']);

    Route::post('/assign-maintenance', [AMCMaintenanceController::class, 'assignMaintenance']);

    Route::get('/tracks-active/{technician_id}', [TrackController::class, 'getActive']);
    Route::get('tracks/{track_id}', [TrackController::class, 'show']);
    Route::get('/tracks', [TrackController::class, 'index']);
//});



// Admin routes
//Route::middleware(['auth:sanctum','admin'])->group(function () {
    Route::get('/dashboard/admin', [DashboardController::class, 'adminDashboard']);
//});

// Operator routes
//Route::middleware(['auth:sanctum','operator'])->group(function () {
    Route::get('/dashboard/operator', [DashboardController::class, 'operatorDashboard']);
//});

// Technician routes
//Route::middleware(['auth:sanctum', 'technician'])->group(function () {
    Route::get('/dashboard/technician', [DashboardController::class, 'technicianDashboard']);

    //Ticket
    Route::post('/accept-ticket', [TicketController::class, 'acceptTicket']);
    Route::post('/complete-ticket', [TicketController::class, 'completeTicket']);
    Route::get('/tickets/assigned/{assigned_to}', [TicketController::class, 'getTicketsByAssignedTo']);

    Route::get('/amc-maintenances/assigned/{assigned_to}', [AMCMaintenanceController::class, 'getMaintenancesByAssignedTo']);

    Route::get('/shop-inventories-all', [ShopInventoryController::class, 'getAllShopInventoriesRaw']);

    Route::post('/inventory-usages', [InventoryItemUsageController::class, 'createUsage']);
    Route::post('/inventory-returns', [InventoryItemUsageController::class, 'returnItems']);

    Route::post('/tracks-start', [TrackController::class, 'start']);
    Route::post('/tracks-points', [TrackController::class, 'savePoints']);
    Route::post('/{track_id}/stop', [TrackController::class, 'stop']);
//});

// Customer routes
//Route::middleware(['auth:sanctum', 'customer'])->group(function () {

    //Ticket
    Route::post('/create-ticket', [TicketController::class, 'createTicket']);
    Route::get('/tickets-customer/{customer_id}', [TicketController::class, 'getTicketsByCustomer']);

//});
