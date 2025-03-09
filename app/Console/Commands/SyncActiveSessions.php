<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Models\Customer;
use App\Models\ActiveSession;
use App\Services\MikrotikService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncActiveSessions extends Command
{
    protected $signature = 'hotspot:sync-sessions';
    protected $description = 'Sync active hotspot sessions from Mikrotik routers';

    public function handle(MikrotikService $mikrotik)
    {
        $this->info('Starting active session sync...');

        Router::where('status', 'online')->chunk(10, function($routers) use ($mikrotik) {
            foreach ($routers as $router) {
                try {
                    $this->info("Syncing sessions for router: {$router->name}");
                    
                    // Connect to router
                    $client = $mikrotik->connect($router);
                    
                    // Get active sessions from Mikrotik
                    $printRequest = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/active/print');
                    $sessions = $client->sendSync($printRequest);
                    
                    // Process each session
                    foreach ($sessions as $session) {
                        $username = $session['user'] ?? null;
                        if (!$username) continue;
                        
                        // Find or create customer by username
                        $customer = Customer::firstOrCreate(
                            ['username' => $username],
                            [
                                'name' => 'Hotspot User',
                                'password' => \Str::random(8),
                                'service_type' => 'hotspot',
                                'is_active' => true
                            ]
                        );
                        
                        // Update or create session record
                        ActiveSession::updateOrCreate(
                            [
                                'customer_id' => $customer->id,
                                'router_id' => $router->id,
                                'username' => $username,
                                'mac_address' => $session['mac-address'] ?? '',
                            ],
                            [
                                'ip_address' => $session['address'] ?? null,
                                'bytes_in' => $session['bytes-in'] ?? 0,
                                'bytes_out' => $session['bytes-out'] ?? 0,
                                'uptime_seconds' => $this->parseUptime($session['uptime'] ?? '0s'),
                                'last_activity' => now(),
                            ]
                        );
                    }
                    
                    // Remove stale sessions
                    ActiveSession::where('router_id', $router->id)
                        ->where('last_activity', '<', now()->subMinutes(5))
                        ->delete();
                        
                    $this->info("Successfully synced " . count($sessions) . " sessions");
                    
                } catch (\Exception $e) {
                    $this->error("Failed to sync sessions for router {$router->name}: " . $e->getMessage());
                    Log::error('Session sync failed', [
                        'router' => $router->name,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        $this->info('Session sync completed');
    }

    protected function parseUptime($uptime)
    {
        $seconds = 0;
        
        if (preg_match('/(\d+)w/', $uptime, $matches)) {
            $seconds += $matches[1] * 7 * 24 * 3600;
        }
        if (preg_match('/(\d+)d/', $uptime, $matches)) {
            $seconds += $matches[1] * 24 * 3600;
        }
        if (preg_match('/(\d+)h/', $uptime, $matches)) {
            $seconds += $matches[1] * 3600;
        }
        if (preg_match('/(\d+)m/', $uptime, $matches)) {
            $seconds += $matches[1] * 60;
        }
        if (preg_match('/(\d+)s/', $uptime, $matches)) {
            $seconds += $matches[1];
        }
        
        return $seconds;
    }
}
