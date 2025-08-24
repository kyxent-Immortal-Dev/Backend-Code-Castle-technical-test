<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;

class DocumentationController extends Controller
{
    /**
     * Serve the Swagger documentation YAML file
     */
    public function yaml()
    {
        $yamlPath = storage_path('api-docs/api-docs.yaml');
        
        if (!File::exists($yamlPath)) {
            abort(404, 'Documentation file not found');
        }
        
        $content = File::get($yamlPath);
        
        return response($content, 200, [
            'Content-Type' => 'text/yaml',
            'Content-Disposition' => 'inline; filename="api-docs.yaml"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
    
    /**
     * Serve the Swagger documentation JSON file
     */
    public function json()
    {
        $jsonPath = storage_path('api-docs/api-docs.json');
        
        if (!File::exists($jsonPath)) {
            abort(404, 'Documentation file not found');
        }
        
        $content = File::get($jsonPath);
        
        return response($content, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'inline; filename="api-docs.json"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
} 