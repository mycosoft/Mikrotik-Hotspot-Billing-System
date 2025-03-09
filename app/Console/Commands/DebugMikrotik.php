<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;
use App\Models\Router;
use RouterOS\Client;

class DebugMikrotik extends Command
{
    protected $signature = 'debug:mikrotik {router_id}';
    protected $description = 'Debug MikroTik connection and configuration';

    public function handle()
    {
        $router = Router::findOrFail($this->argument('router_id'));

        $this->info("\n=== Router Details ===");
        $this->info("Name: " . $router->name);
        $this->info("IP: " . $router->ip_address);
        $this->info("Port: " . ($router->port ?? 8728));
        $this->info("Username: " . $router->username);
        $this->info("Status: " . ($router->is_active ? 'Active' : 'Inactive'));

        $this->info("\nTesting connection...");

        try {
            $client = new Client([
                'host' => $router->ip_address,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->port ?? 8728,
                'timeout' => 10
            ]);

            // Check system identity
            $this->info("\n=== Router Identity ===");
            $identity = $client->query('/system/identity/print')->read();
            $this->info(json_encode($identity, JSON_PRETTY_PRINT));

            // Check API connection
            $this->info("\n=== API Service ===");
            $api = $client->query('/ip/service/print', ['?name=api'])->read();
            $this->info(json_encode($api, JSON_PRETTY_PRINT));

            // Check hotspot service
            $this->info("\n=== Hotspot Service ===");
            $hotspot = $client->query('/ip/hotspot/print')->read();
            $this->info(json_encode($hotspot, JSON_PRETTY_PRINT));

            // Check hotspot server
            $this->info("\n=== Hotspot Server ===");
            $server = $client->query('/ip/hotspot/server/print')->read();
            $this->info(json_encode($server, JSON_PRETTY_PRINT));

            // Check user profiles
            $this->info("\n=== Hotspot User Profiles ===");
            $profiles = $client->query('/ip/hotspot/user/profile/print')->read();
            $this->info(json_encode($profiles, JSON_PRETTY_PRINT));

            // Check queues
            $this->info("\n=== Simple Queues ===");
            $queues = $client->query('/queue/simple/print')->read();
            $this->info(json_encode($queues, JSON_PRETTY_PRINT));

            $this->info("\n=== Connection Test SUCCESSFUL ===");

        } catch (\Exception $e) {
            $this->error("\nConnection failed!");
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . " (Line: " . $e->getLine() . ")");
            $this->error("\nStack trace:");
            $this->error($e->getTraceAsString());
        }
    }
} 