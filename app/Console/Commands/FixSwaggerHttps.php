<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixSwaggerHttps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:fix-https {--force : Force regeneration of all files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix HTTPS issues in Swagger documentation for Railway deployment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing Swagger HTTPS issues...');

        // Check if we're in production
        if (app()->environment('production')) {
            $this->info('🌐 Production environment detected');
        } else {
            $this->info('🏠 Local environment detected');
        }

        // Clear all caches
        $this->info('🧹 Clearing Laravel caches...');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('cache:clear');
        $this->call('view:clear');

        // Update Swagger documentation
        $this->info('📚 Updating Swagger documentation...');
        $this->call('swagger:update', ['--environment' => app()->environment('production') ? 'production' : 'local']);

        // Force HTTPS in YAML files
        $this->info('🔒 Forcing HTTPS in documentation files...');
        $this->forceHttpsInFiles();

        // Verify files
        $this->info('✅ Verifying documentation files...');
        $this->verifyFiles();

        $this->info('🎉 Swagger HTTPS issues fixed!');
        $this->info('');
        $this->info('📝 Next steps:');
        $this->info('   1. Commit and push your changes');
        $this->info('   2. Deploy to Railway: railway up');
        $this->info('   3. Verify at: ' . env('APP_URL', 'https://your-app.railway.app') . '/api/documentation');

        return 0;
    }

    /**
     * Force HTTPS in all documentation files
     */
    private function forceHttpsInFiles()
    {
        $files = [
            storage_path('api-docs/api-docs.yaml'),
            base_path('docs/openapi.yaml'),
        ];

        foreach ($files as $file) {
            if (File::exists($file)) {
                $content = File::get($file);
                $originalContent = $content;

                // Replace all HTTP URLs with HTTPS
                $content = str_replace('http://', 'https://', $content);

                // Update server URLs
                $appUrl = env('APP_URL', 'https://localhost');
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

                // Add schemes to force HTTPS
                if (!str_contains($content, 'schemes:')) {
                    $content = str_replace(
                        'openapi: 3.0.3',
                        "openapi: 3.0.3\nservers:\n  - url: \"{$appUrl}/api\"\n    description: Production Server\n  - url: \"http://localhost:8000/api\"\n    description: Local Development Server",
                        $content
                    );
                }

                if ($content !== $originalContent) {
                    File::put($file, $content);
                    $this->info("   ✅ Updated: " . basename($file));
                } else {
                    $this->info("   ℹ️  No changes needed: " . basename($file));
                }
            }
        }
    }

    /**
     * Verify that documentation files exist and are accessible
     */
    private function verifyFiles()
    {
        $files = [
            'storage/api-docs/api-docs.yaml' => storage_path('api-docs/api-docs.yaml'),
            'docs/openapi.yaml' => base_path('docs/openapi.yaml'),
        ];

        foreach ($files as $name => $path) {
            if (File::exists($path)) {
                $size = File::size($path);
                $this->info("   ✅ {$name} exists ({$size} bytes)");
            } else {
                $this->error("   ❌ {$name} not found");
            }
        }

        // Check routes
        $this->info('   🔍 Checking routes...');
        $this->call('route:list', ['--name' => 'docs']);
    }
} 