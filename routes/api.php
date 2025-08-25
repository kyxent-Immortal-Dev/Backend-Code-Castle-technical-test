<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AuthController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Inventary\ProductController;
use App\Http\Controllers\Inventary\SupplierController;
use App\Http\Controllers\Inventary\PurchaseController;
use App\Http\Controllers\Sales\ClientController;
use App\Http\Controllers\Sales\SaleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware(['auth:sanctum', 'auth.errors'])->group(function () {
    
    // User profile routes
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // Admin-only user management routes
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    });
    
    // Admin-only inventory management routes
    Route::middleware('role:admin')->group(function () {
        // Products management - Specific routes first, then resource routes
        Route::get('/products/stock-report', [ProductController::class, 'generateStockReport']);
        Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
        Route::get('/products/out-of-stock', [ProductController::class, 'outOfStock']);
        Route::get('/products/stats', [ProductController::class, 'stats']);
        Route::get('/products/price-range', [ProductController::class, 'byPriceRange']);
        Route::apiResource('products', ProductController::class);
        Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
        
        // Suppliers management
        Route::apiResource('suppliers', SupplierController::class);
        Route::patch('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus']);
        Route::get('/suppliers/active', [SupplierController::class, 'active']);
        Route::get('/suppliers/top', [SupplierController::class, 'topSupplier']);
        Route::get('/suppliers/by-amount', [SupplierController::class, 'byTotalAmount']);
        Route::get('/suppliers/stats', [SupplierController::class, 'stats']);
        Route::get('/suppliers/search', [SupplierController::class, 'search']);
        
        // Purchases management - Specific routes first, then resource routes
        Route::get('/purchases/stats', [PurchaseController::class, 'stats']);
        Route::get('/purchases/status/{status}', [PurchaseController::class, 'byStatus']);
        Route::get('/purchases/supplier/{supplierId}', [PurchaseController::class, 'bySupplier']);
        Route::get('/purchases/date-range', [PurchaseController::class, 'byDateRange']);
        Route::get('/purchases/monthly', [PurchaseController::class, 'monthlyTotals']);
        Route::get('/purchases/top-products', [PurchaseController::class, 'topProducts']);
        Route::get('/purchases/supplier/{supplierId}/report', [PurchaseController::class, 'generatePurchasesBySupplierReport']);
        Route::apiResource('purchases', PurchaseController::class)->except(['update']);
        Route::put('/purchases/{purchase}', [PurchaseController::class, 'update']);
        Route::patch('/purchases/{purchase}/complete', [PurchaseController::class, 'complete']);
        Route::patch('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel']);
        
        // Clients management
        Route::apiResource('clients', ClientController::class);
        Route::patch('/clients/{client}/toggle-status', [ClientController::class, 'toggleStatus']);
        Route::get('/clients/active', [ClientController::class, 'active']);
        Route::get('/clients/search', [ClientController::class, 'search']);
        Route::get('/clients/stats', [ClientController::class, 'stats']);
        
        // Sales management - Specific routes first, then resource routes
        Route::get('/sales/stats', [SaleController::class, 'stats']);
        Route::get('/sales/status/{status}', [SaleController::class, 'byStatus']);
        Route::get('/sales/client/{clientId}', [SaleController::class, 'byClient']);
        Route::get('/sales/user/{userId}', [SaleController::class, 'byUser']);
        Route::get('/sales/date-range', [SaleController::class, 'byDateRange']);
        Route::get('/sales/monthly', [SaleController::class, 'monthlyTotals']);
        Route::get('/sales/top-products', [SaleController::class, 'topProducts']);
        Route::get('/sales/report', [SaleController::class, 'generateSalesReport']);
        Route::apiResource('sales', SaleController::class)->except(['update', 'destroy']);
        Route::patch('/sales/{sale}/cancel', [SaleController::class, 'cancel']);
    });
    
    // Authenticated user routes (read-only access to inventory)
    Route::middleware('auth')->group(function () {
        // Products - read access only
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        
        // Suppliers - read access only
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
        
        // Purchases - read access only
        Route::get('/purchases', [PurchaseController::class, 'index']);
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show']);
        
        // Clients - read access only
        Route::get('/clients', [ClientController::class, 'index']);
        Route::get('/clients/{client}', [ClientController::class, 'show']);
        
        // Sales - read access only
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{sale}', [SaleController::class, 'show']);
    });
    
    // Vendedor routes (if needed in the future)
    Route::middleware('role:vendedor')->group(function () {

    });
});

// Legacy route (can be removed if not needed)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'auth.errors']);