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
    protected $signature = 'swagger:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Swagger documentation with the complete YAML file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Updating Swagger documentation...');

        // Source file (our complete YAML)
        $sourceFile = storage_path('api-docs/api-docs.yaml');
        
        // Destination files
        $destinations = [
            'docs/openapi.yaml',
            'storage/app/public/api-docs.yaml',
        ];

        if (!File::exists($sourceFile)) {
            $this->error('❌ Source file not found: ' . $sourceFile);
            return 1;
        }

        $successCount = 0;
        foreach ($destinations as $destination) {
            $destinationPath = base_path($destination);
            
            // Create directory if it doesn't exist
            $destinationDir = dirname($destinationPath);
            if (!File::exists($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true);
            }

            try {
                File::copy($sourceFile, $destinationPath);
                $this->info("✅ Copied to: {$destination}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("❌ Failed to copy to {$destination}: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $this->info("🎉 Successfully updated {$successCount} documentation files!");
            $this->info("📚 Your complete inventory module documentation is now available!");
            $this->info("🌐 Access at: http://localhost:8000/api/documentation");
        } else {
            $this->error("❌ No files were updated successfully");
            return 1;
        }

        return 0;
    }
}
