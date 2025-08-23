<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
// SwaggerController removed - using L5-Swagger built-in routes

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
    
    // Vendedor routes (if needed in the future)
    Route::middleware('role:vendedor')->group(function () {
        // Add vendedor-specific routes here
    });
});

// Legacy route (can be removed if not needed)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'auth.errors']);
