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
        
        // Force HTTPS URLs in production
        if (app()->environment('production')) {
            $content = $this->forceHttpsUrls($content);
        }
        
        return response($content, 200, [
            'Content-Type' => 'text/yaml',
            'Content-Disposition' => 'inline; filename="api-docs.yaml"',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type',
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
        
        // Force HTTPS URLs in production
        if (app()->environment('production')) {
            $content = $this->forceHttpsUrls($content);
        }
        
        return response($content, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'inline; filename="api-docs.json"',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type',
        ]);
    }
    
    /**
     * Force HTTPS URLs in documentation content
     */
    private function forceHttpsUrls($content)
    {
        $appUrl = env('APP_URL', 'https://localhost');
        
        // Replace HTTP URLs with HTTPS
        $content = str_replace('http://', 'https://', $content);
        
        // Update server URLs to use current domain
        $content = preg_replace(
            '/url: "https:\/\/[^"]*\/api"/',
            'url: "' . $appUrl . '/api"',
            $content
        );
        
        // Update external docs URL
        $content = preg_replace(
            '/url: "https:\/\/[^"]*\/api\/documentation"/',
            'url: "' . $appUrl . '/api/documentation"',
            $content
        );
        
        return $content;
    }
} 