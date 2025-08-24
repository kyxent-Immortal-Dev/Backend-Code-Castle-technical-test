<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateSwaggerDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:update {--environment=local : Environment to update URLs for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Swagger documentation with the complete YAML file and update URLs for environment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Updating Swagger documentation...');

        // Source file (our complete YAML)
        $sourceFile = storage_path('api-docs/api-docs.yaml');
        
        if (!File::exists($sourceFile)) {
            $this->error('❌ Source file not found: ' . $sourceFile);
            return 1;
        }

        // Read the source content
        $content = File::get($sourceFile);
        
        // Update URLs based on environment
        $env = $this->option('environment');
        if ($env === 'production') {
            $appUrl = env('APP_URL', 'https://your-app.railway.app');
            $content = $this->updateUrls($content, $appUrl);
            $this->info("🌐 Updated URLs for production: {$appUrl}");
        } else {
            $this->info("🏠 Using local development URLs");
        }

        // Destination files
        $destinations = [
            'storage/api-docs/api-docs.yaml' => $content,
            'docs/openapi.yaml' => $content,
        ];

        $successCount = 0;
        foreach ($destinations as $destination => $fileContent) {
            $destinationPath = base_path($destination);
            
            // Create directory if it doesn't exist
            $destinationDir = dirname($destinationPath);
            if (!File::exists($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true);
            }

            try {
                File::put($destinationPath, $fileContent);
                $this->info("✅ Updated: {$destination}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("❌ Failed to update {$destination}: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $this->info("🎉 Successfully updated {$successCount} documentation files!");
            $this->info("📚 Your complete inventory module documentation is now available!");
            
            if ($env === 'production') {
                $this->info("🌐 Production URL: " . env('APP_URL', 'https://your-app.railway.app') . "/api/documentation");
            } else {
                $this->info("🏠 Local URL: http://localhost:8000/api/documentation");
            }
        } else {
            $this->error("❌ No files were updated successfully");
            return 1;
        }

        return 0;
    }

    /**
     * Update URLs in the YAML content for production
     */
    private function updateUrls($content, $appUrl)
    {
        // Remove protocol and port from APP_URL
        $baseUrl = str_replace(['http://', 'https://'], '', $appUrl);
        $baseUrl = preg_replace('/:\d+$/', '', $baseUrl);
        
        // Update server URLs
        $content = preg_replace(
            '/url: "http:\/\/localhost:8000\/api"/',
            'url: "' . $appUrl . '/api"',
            $content
        );
        
        $content = preg_replace(
            '/url: "https:\/\/your-production-domain\.com\/api"/',
            'url: "' . $appUrl . '/api"',
            $content
        );
        
        // Update external docs URL
        $content = preg_replace(
            '/url: "https:\/\/github\.com\/your-repo"/',
            'url: "' . $appUrl . '/api/documentation"',
            $content
        );
        
        return $content;
    }
}
