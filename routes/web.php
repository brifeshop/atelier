<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\CustomerController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\EmployeeController;
use App\Http\Controllers\Master\MachineController;
use App\Http\Controllers\Master\MaterialController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\WorkCenterController;

use App\Http\Controllers\Sales\SalesOrderController;

use App\Http\Controllers\Warehouse\WarehouseController;
use App\Http\Controllers\Warehouse\LocationController;
use App\Http\Controllers\Warehouse\InventoryController;
use App\Http\Controllers\Warehouse\StockMovementController;

use App\Http\Controllers\Purchasing\PurchaseRequisitionController;

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('master')->name('master.')->group(function () {
    Route::resource('customers', CustomerController::class);
});

Route::middleware(['auth', 'verified'])->prefix('master')->name('master.')->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('suppliers', SupplierController::class);
});

Route::middleware(['auth', 'verified'])->prefix('master')->name('master.')->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('employees', EmployeeController::class);
});

Route::middleware(['auth', 'verified'])->prefix('master')->name('master.')->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('machines', MachineController::class);
});

Route::middleware(['auth', 'verified'])->prefix('master')->name('master.')->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('machines', MachineController::class);
    Route::resource('materials', MaterialController::class);
    Route::resource('products', ProductController::class); 
    Route::resource('work-centers', WorkCenterController::class);
});

Route::middleware(['auth', 'verified'])->prefix('sales')->name('sales.')->group(function () {
    Route::resource('sales-orders', SalesOrderController::class);
    Route::patch('sales-orders/{sales_order}/status', [SalesOrderController::class, 'updateStatus'])
        ->name('sales-orders.update-status');
});

Route::middleware(['auth', 'verified'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::resource('warehouses', WarehouseController::class);
});

Route::middleware(['auth', 'verified'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('locations', LocationController::class);   // ← TAMBAHKAN
});

Route::middleware(['auth', 'verified'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('inventories', InventoryController::class)->only(['index', 'show', 'update']);
});

Route::middleware(['auth', 'verified'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('inventories', InventoryController::class)->only(['index', 'show', 'update']);
    Route::resource('stock-movements', StockMovementController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('stock-movements/transfer/create', [StockMovementController::class, 'createTransfer'])
        ->name('stock-movements.transfer.create');
    Route::post('stock-movements/transfer', [StockMovementController::class, 'storeTransfer'])
        ->name('stock-movements.transfer.store');
});

Route::middleware(['auth', 'verified'])->prefix('purchasing')->name('purchasing.')->group(function () {
    Route::resource('purchase-requisitions', PurchaseRequisitionController::class);
    Route::patch('purchase-requisitions/{purchase_requisition}/approve', [PurchaseRequisitionController::class, 'approve'])
        ->name('purchase-requisitions.approve');
    Route::patch('purchase-requisitions/{purchase_requisition}/reject', [PurchaseRequisitionController::class, 'reject'])
        ->name('purchase-requisitions.reject');
});

require __DIR__.'/auth.php';
