<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Models\ActiveSession;
use App\Services\MikrotikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorActiveSessions extends Command
{
    protected $signature = 'hotspot:monitor-sessions';
    protected $description = 'Monitor and sync active hotspot sessions';

    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        parent::__construct();
        $this->mikrotikService = $mikrotikService;
    }

    public function handle()
    {
        $this->info('Starting active session monitoring...');
        
        Router::where('is_active', true)->chunk(10, function ($routers) {
            foreach ($routers as $router) {
                try {
                    $this->processRouter($router);
                } catch (\Exception $e) {
                    Log::error('Failed to process router: ' . $router->name, [
                        'router_id' => $router->id,
                        'error' => $e->getMessage()
                    ]);
                    $this->error("Failed to process router {$router->name}: {$e->getMessage()}");
                }
            }
        });

        $this->info('Session monitoring completed.');
    }

    protected function processRouter(Router $router)
    {
        $this->info("Processing router: {$router->name}");

        // Connect to router
        if (!$this->mikrotikService->connect($router)) {
            throw new \Exception('Could not connect to router');
        }

        // Get active sessions from router
        $routerSessions = $this->mikrotikService->getActiveSessions();
        $routerSessionIds = collect($routerSessions)->pluck('session_id')->toArray();

        // Get local sessions for this router
        $localSessions = $router->activeSessions;
        $localSessionIds = $localSessions->pluck('session_id')->toArray();

        // Find sessions to remove (exist locally but not on router)
        $sessionsToRemove = array_diff($localSessionIds, $routerSessionIds);
        if (!empty($sessionsToRemove)) {
            ActiveSession::whereIn('session_id', $sessionsToRemove)->delete();
            $this->info(count($sessionsToRemove) . ' stale sessions removed');
        }

        // Update or create sessions from router
        foreach ($routerSessions as $sessionData) {
            $router->activeSessions()->updateOrCreate(
                ['session_id' => $sessionData['session_id']],
                $sessionData
            );
        }

        // Check for expired vouchers and disconnect if necessary
        $expiredSessions = $router->activeSessions()
            ->whereHas('customer.vouchers', function ($query) {
                $query->where('expires_at', '<', now());
            })
            ->get();

        foreach ($expiredSessions as $session) {
            try {
                if ($this->mikrotikService->disconnectSession($session)) {
                    $session->delete();
                    $this->info("Disconnected expired session: {$session->session_id}");
                }
            } catch (\Exception $e) {
                Log::warning("Failed to disconnect expired session: {$session->session_id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Check and update bandwidth limits
        $activeSessions = $router->activeSessions()
            ->with(['customer.vouchers.plan.bandwidthProfile'])
            ->get();

        foreach ($activeSessions as $session) {
            try {
                $voucher = $session->customer->vouchers()
                    ->where('is_used', true)
                    ->where('expires_at', '>', now())
                    ->first();

                if ($voucher && $voucher->plan && $voucher->plan->bandwidthProfile) {
                    $this->mikrotikService->applyBandwidthProfile(
                        $session->ip_address,
                        $voucher->plan->bandwidthProfile->getMikrotikQueueConfig()
                    );
                }
            } catch (\Exception $e) {
                Log::warning("Failed to update bandwidth for session: {$session->session_id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Completed processing router: {$router->name}");
    }
}
