<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;

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
        Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    });
    
    // Admin-only inventory management routes
    Route::middleware('role:admin')->group(function () {
        // Products management
        Route::apiResource('products', ProductController::class);
        Route::patch('/products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
        Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
        Route::get('/products/out-of-stock', [ProductController::class, 'outOfStock']);
        Route::get('/products/stats', [ProductController::class, 'stats']);
        Route::get('/products/price-range', [ProductController::class, 'byPriceRange']);
        
        // Suppliers management
        Route::apiResource('suppliers', SupplierController::class);
        Route::patch('/suppliers/{id}/toggle-status', [SupplierController::class, 'toggleStatus']);
        Route::get('/suppliers/active', [SupplierController::class, 'active']);
        Route::get('/suppliers/top', [SupplierController::class, 'topSupplier']);
        Route::get('/suppliers/by-amount', [SupplierController::class, 'byTotalAmount']);
        Route::get('/suppliers/stats', [SupplierController::class, 'stats']);
        Route::get('/suppliers/search', [SupplierController::class, 'search']);
        
        // Purchases management
        Route::apiResource('purchases', PurchaseController::class)->except(['update']);
        Route::patch('/purchases/{id}/complete', [PurchaseController::class, 'complete']);
        Route::patch('/purchases/{id}/cancel', [PurchaseController::class, 'cancel']);
        Route::get('/purchases/status/{status}', [PurchaseController::class, 'byStatus']);
        Route::get('/purchases/date-range', [PurchaseController::class, 'byDateRange']);
        Route::get('/purchases/supplier/{supplierId}', [PurchaseController::class, 'bySupplier']);
        Route::get('/purchases/stats', [PurchaseController::class, 'stats']);
        Route::get('/purchases/monthly', [PurchaseController::class, 'monthlyTotals']);
        Route::get('/purchases/top-products', [PurchaseController::class, 'topProducts']);
    });
    
    // Authenticated user routes (read-only access to inventory)
    Route::middleware('auth')->group(function () {
        // Products - read access only
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        
        // Suppliers - read access only
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
        
        // Purchases - read access only
        Route::get('/purchases', [PurchaseController::class, 'index']);
        Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
    });
    
    // Vendedor routes (if needed in the future)
    Route::middleware('role:vendedor')->group(function () {
        // Add vendedor-specific routes here
        // Could include read access to products and basic inventory info
    });
});

// Legacy route (can be removed if not needed)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'auth.errors']);
