<?php

namespace App\Console\Commands;

use App\Models\ActiveSession;
use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredSessions extends Command
{
    protected $signature = 'hotspot:cleanup-sessions';
    protected $description = 'Clean up expired sessions and disconnect users';

    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        parent::__construct();
        $this->mikrotikService = $mikrotikService;
    }

    public function handle()
    {
        $this->info('Starting cleanup of expired sessions...');

        // Get all active routers
        Router::where('is_active', true)->chunk(10, function ($routers) {
            foreach ($routers as $router) {
                try {
                    $this->cleanupRouterSessions($router);
                } catch (\Exception $e) {
                    Log::error('Failed to cleanup router sessions: ' . $router->name, [
                        'router_id' => $router->id,
                        'error' => $e->getMessage()
                    ]);
                    $this->error("Failed to cleanup router {$router->name}: {$e->getMessage()}");
                }
            }
        });

        // Cleanup orphaned sessions
        $deletedOrphans = ActiveSession::whereDoesntHave('router')->delete();
        if ($deletedOrphans > 0) {
            $this->info("Cleaned up {$deletedOrphans} orphaned sessions");
        }

        $this->info('Session cleanup completed.');
    }

    protected function cleanupRouterSessions(Router $router)
    {
        $this->info("Processing router: {$router->name}");

        // Get expired sessions
        $expiredSessions = $router->activeSessions()
            ->whereHas('customer.vouchers', function ($query) {
                $query->where('expires_at', '<', now());
            })
            ->get();

        if ($expiredSessions->isEmpty()) {
            $this->info("No expired sessions found for router: {$router->name}");
            return;
        }

        // Connect to router
        if (!$this->mikrotikService->connect($router)) {
            throw new \Exception('Could not connect to router');
        }

        $disconnectedCount = 0;
        foreach ($expiredSessions as $session) {
            try {
                if ($this->mikrotikService->disconnectSession($session)) {
                    $session->delete();
                    $disconnectedCount++;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to disconnect session: {$session->session_id}", [
                    'router_id' => $router->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Disconnected {$disconnectedCount} expired sessions from router: {$router->name}");
    }
}
