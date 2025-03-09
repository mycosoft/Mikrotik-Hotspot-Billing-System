<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Console\Command;

class UploadHotspotFiles extends Command
{
    protected $signature = 'hotspot:upload-files {router : The ID or name of the router} {--source=BNET-WIFI : Source directory name}';
    protected $description = 'Upload hotspot files to a MikroTik router';

    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        parent::__construct();
        $this->mikrotikService = $mikrotikService;
    }

    public function handle()
    {
        $routerIdentifier = $this->argument('router');
        $sourceDir = $this->option('source');

        // Find the router
        $router = is_numeric($routerIdentifier) 
            ? Router::find($routerIdentifier)
            : Router::where('name', $routerIdentifier)->first();

        if (!$router) {
            $this->error("Router not found!");
            return 1;
        }

        // Check if source directory exists
        $sourcePath = base_path("../BNET-WIFI");
        if (!is_dir($sourcePath)) {
            $this->error("Source directory not found: {$sourcePath}");
            return 1;
        }

        $this->info("Uploading hotspot files to router {$router->name}...");

        // Upload the files
        $result = $this->mikrotikService->uploadDirectory($router, $sourcePath, "BNET-WIFI");

        if ($result['success']) {
            $this->info("Files uploaded successfully!");
            return 0;
        } else {
            $this->error("Failed to upload files: " . $result['message']);
            return 1;
        }
    }
}
