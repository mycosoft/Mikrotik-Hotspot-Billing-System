<?php

namespace App\Services;

use App\Models\Router;
use PEAR2\Net\RouterOS\Client;
use PEAR2\Net\RouterOS\Request;
use Exception;
use Carbon\Carbon;

class RouterService
{
    /**
     * Test connection to a router
     *
     * @param Router $router
     * @return array
     */
    public function testConnection(Router $router)
    {
        try {
            $result = $router->testConnection();
            
            return [
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
                'status' => $router->getStatusText(),
                'statusClass' => $router->getStatusBadgeClass(),
                'lastSeen' => $router->getLastSeenHuman()
            ];
        } catch (Exception $e) {
            \Log::error('Router connection failed: ' . $e->getMessage(), [
                'router_id' => $router->id,
                'ip_address' => $router->ip_address,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to connect to router: ' . $e->getMessage(),
                'status' => $router->getStatusText(),
                'statusClass' => $router->getStatusBadgeClass(),
                'lastSeen' => $router->getLastSeenHuman()
            ];
        }
    }

    /**
     * Check online status of all active routers
     *
     * @return void
     */
    public function checkAllRoutersStatus()
    {
        $routers = Router::where('is_active', true)->get();
        
        foreach ($routers as $router) {
            try {
                $router->testConnection();
            } catch (Exception $e) {
                \Log::error('Router status check failed: ' . $e->getMessage(), [
                    'router_id' => $router->id,
                    'ip_address' => $router->ip_address,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get router system resources
     *
     * @param Router $router
     * @return array
     */
    public function getSystemResources(Router $router)
    {
        try {
            $client = $router->getClient();
            $printRequest = new Request('/system/resource/print');
            $response = $client->sendSync($printRequest);
            
            if (!$response) {
                throw new Exception('No response from router');
            }
            
            // Get system info
            $systemInfo = [
                'uptime' => $response->getProperty('uptime'),
                'version' => $response->getProperty('version'),
                'cpu-load' => $response->getProperty('cpu-load'),
                'free-memory' => $response->getProperty('free-memory'),
                'total-memory' => $response->getProperty('total-memory'),
                'cpu-count' => $response->getProperty('cpu-count'),
                'board-name' => $response->getProperty('board-name'),
                'platform' => $response->getProperty('platform')
            ];
            
            return [
                'success' => true,
                'data' => $systemInfo
            ];
        } catch (Exception $e) {
            \Log::error('Failed to get router resources: ' . $e->getMessage(), [
                'router_id' => $router->id,
                'ip_address' => $router->ip_address,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
