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

require __DIR__.'/auth.php';
