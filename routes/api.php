<?php

use App\Http\Controllers\AdminMaintenanceController;
use App\Http\Controllers\AMCContractController;
use App\Http\Controllers\AMCMaintenanceController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\InventoryItemUsageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperatorMaintenanceController;
use App\Http\Controllers\ProductDropdownController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShopInventoryController;
use App\Http\Controllers\TechnicianController;
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

    // Notifications (all roles)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
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

    // Technician users
    Route::get('/all-technician-users', [UserController::class, 'getAllTechnicianUsers']);

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

    Route::post('/assign-maintenance', [AMCMaintenanceController::class, 'assignMaintenance']);

    Route::get('/tracks-active/{technician_id}', [TrackController::class, 'getActive']);
    Route::get('tracks/{track_id}', [TrackController::class, 'show']);
    Route::get('/tracks', [TrackController::class, 'index']);

    /*
    | Admin & Operator maintenance (4.2, 4.3)
    */
    Route::apiResource('admins', AdminMaintenanceController::class)->except(['create', 'edit']);
    Route::post('admins/{id}/activate', [AdminMaintenanceController::class, 'activate']);
    Route::post('admins/{id}/deactivate', [AdminMaintenanceController::class, 'deactivate']);

    Route::apiResource('operators', OperatorMaintenanceController::class)->except(['create', 'edit']);
    Route::post('operators/{id}/activate', [OperatorMaintenanceController::class, 'activate']);
    Route::post('operators/{id}/deactivate', [OperatorMaintenanceController::class, 'deactivate']);

    /*
    | Product dropdown maintenance (4.7)
    */
    Route::get('product-dropdowns', [ProductDropdownController::class, 'index']);
    Route::post('product-dropdowns', [ProductDropdownController::class, 'store']);
    Route::get('product-dropdowns/{id}', [ProductDropdownController::class, 'show']);
    Route::put('product-dropdowns/{id}', [ProductDropdownController::class, 'update']);
    Route::delete('product-dropdowns/{id}', [ProductDropdownController::class, 'destroy']);

    /*
    | Notifications control (4.11) + demo trigger
    */
    Route::post('/notifications/test-expiry', [NotificationController::class, 'triggerTestExpiry']);

    /*
    | Reports (4.12)
    */
    Route::get('/reports/tickets', [ReportController::class, 'ticketReport']);
    Route::get('/reports/inventory', [ReportController::class, 'inventoryStockReport']);
    Route::get('/reports/warranty-expiry', [ReportController::class, 'warrantyExpiryReport']);
    Route::get('/reports/amc-expiry', [ReportController::class, 'amcExpiryReport']);
    Route::get('/reports/operator-activity', [ReportController::class, 'operatorActivityReport']);
});



// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Operator maintenance (4.3)
    Route::get('operators', [OperatorMaintenanceController::class, 'index']);
    Route::post('operators', [OperatorMaintenanceController::class, 'store']);
    Route::put('operators/{id}', [OperatorMaintenanceController::class, 'update']);
    Route::delete('operators/{id}', [OperatorMaintenanceController::class, 'destroy']);
    Route::post('operators/{id}/activate', [OperatorMaintenanceController::class, 'activate']);
    Route::post('operators/{id}/deactivate', [OperatorMaintenanceController::class, 'deactivate']);

    // Ticket management views (4.9, 4.10)
    Route::get('/admin/tickets', [TicketController::class, 'indexForOperator']);
    Route::get('/admin/tickets/{id}', [TicketController::class, 'show']);
    Route::patch('/admin/tickets/{id}/status', [TicketController::class, 'updateStatus']);
    Route::post('/admin/tickets/{id}/assign-technician', [TicketController::class, 'assignTicketToTechnician']);
});

/*
|--------------------------------------------------------------------------
| Operator routes (4.3, 4.9, 4.10, 4.11)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'operator'])->group(function () {

    // Ticket workflow
    Route::get('/operator/tickets', [TicketController::class, 'indexForOperator']);
    Route::get('/operator/tickets/{id}', [TicketController::class, 'show']);
    Route::patch('/operator/tickets/{id}/status', [TicketController::class, 'updateStatus']);
    Route::post('/operator/tickets/{id}/assign-technician', [TicketController::class, 'assignTicketToTechnician']);

    // Optional: record feedback after maintenance
    Route::post('/operator/tickets/{id}/feedback', [TicketController::class, 'storeCustomerFeedback']);

    // See technician tracks
    Route::get('/operator/technicians/{technician_id}/tracks', [TrackController::class, 'index']);
    Route::get('/operator/technicians/{technician_id}/active-track', [TrackController::class, 'getActive']);
});

/*
|--------------------------------------------------------------------------
| Technician routes (4.4, 4.10, 4.14)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'technician'])->group(function () {

    // Ticket
    Route::post('/accept-ticket', [TicketController::class, 'acceptTicket']);
    Route::post('/complete-ticket', [TicketController::class, 'completeTicket']);
    Route::get('/tickets/assigned/{assigned_to}', [TicketController::class, 'getTicketsByAssignedTo']);

    // Technician-specific actions
    Route::post('/tickets/{id}/arrive', [TechnicianController::class, 'arrive']);
    Route::post('/tickets/{id}/attachments', [TechnicianController::class, 'uploadAttachment']);
    Route::get('/tickets/history', [TechnicianController::class, 'history']);

    // Inventory
    Route::get('/shop-inventories-all', [ShopInventoryController::class, 'getAllShopInventoriesRaw']);
    Route::post('/inventory-usages', [InventoryItemUsageController::class, 'createUsage']);
    Route::post('/inventory-returns', [InventoryItemUsageController::class, 'returnItems']);

    // Tracking
    Route::post('/tracks-start', [TrackController::class, 'start']);
    Route::post('/tracks-points', [TrackController::class, 'savePoints']);
    Route::post('/tracks/{track_id}/stop', [TrackController::class, 'stop']); // <-- fixed URI
});

/*
|--------------------------------------------------------------------------
| Customer routes (4.13)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'customer'])->group(function () {

    // Ticket
    Route::post('/create-ticket', [TicketController::class, 'createTicket']);
    Route::get('/tickets-customer/{customer_id}', [TicketController::class, 'getTicketsByCustomer']);

    // Customer portal
    Route::get('/service-history', [CustomerPortalController::class, 'serviceHistory']);
    Route::put('/profile', [CustomerPortalController::class, 'updateProfile']);
    Route::post('/tickets/{ticket_id}/attachments', [CustomerPortalController::class, 'uploadTicketAttachment']);
});

/*
|--------------------------------------------------------------------------
| Public dropdowns for UI (brands, models, issues…)
|--------------------------------------------------------------------------
*/
Route::get('/public/product-dropdowns/{type}', [ProductDropdownController::class, 'listByType']);
