<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

// Route to serve YAML file
Route::get('/api/docs/yaml', function () {
    $yamlPath = storage_path('api-docs/api-docs.yaml');
    
    if (!file_exists($yamlPath)) {
        abort(404, 'API documentation not found. Run: php artisan api:docs');
    }
    
    return response()->file($yamlPath, [
        'Content-Type' => 'application/x-yaml',
        'Access-Control-Allow-Origin' => '*',
    ]);
})->name('api.docs.yaml');

// Custom Swagger UI route
Route::get('/api/documentation', function () {
    $yamlPath = storage_path('api-docs/api-docs.yaml');
    
    if (!file_exists($yamlPath)) {
        abort(404, 'API documentation not found. Run: php artisan api:docs');
    }
    
    $yamlContent = file_get_contents($yamlPath);
    
    return view('swagger-ui', [
        'yamlContent' => $yamlContent,
        'yamlUrl' => url('/api/docs/yaml')
    ]);
})->name('api.documentation');
