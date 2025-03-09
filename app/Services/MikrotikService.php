<?php

namespace App\Services;

use App\Models\Router;
use App\Models\Customer;
use App\Models\ActiveSession;
use App\Models\InternetPlan;
use RouterOS\Client;
use RouterOS\Query;
use Exception;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    protected $client;
    protected $router;

    /**
     * Connect to router
     */
    public function connect(Router $router)
    {
        try {
            $parts = explode(':', $router->ip);
            $client = new Client([
                'host' => $parts[0],
                'port' => (int)(isset($parts[1]) ? $parts[1] : 8728),
                'user' => $router->username,
                'pass' => $router->password
            ]);
            
            $response = $client->query('/system/identity/print')->read();
            $router->update([
                'status' => 'online', 
                'is_online' => true
            ]);
            
            return $client;

        } catch (\Exception $e) {
            $router->update([
                'status' => 'offline', 
                'is_online' => false
            ]);
            
            $error = $e->getMessage();
            if (str_contains($error, 'Parameter')) {
                $error = 'Invalid router configuration. Please check IP and port.';
            } else if (str_contains($error, 'Connection refused')) {
                $error = 'Router refused connection. Check if API service is enabled.';
            } else if (str_contains($error, 'Network is unreachable')) {
                $error = 'Router network is unreachable. Check your connection.';
            } else if (str_contains($error, 'Connection timed out')) {
                $error = 'Connection timed out. Router may be busy or unreachable.';
            } else if (str_contains($error, 'No response')) {
                $error = 'Router is not responding properly';
            }
            
            throw new \Exception($error);
        }
    }

    /**
     * Test connection to router
     */
    public function testConnection(Router $router)
    {
        try {
            $this->connect($router);
            return [
                'success' => true,
                'message' => 'Connected successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add a hotspot plan exactly like PHPNuxBill
     */
    private function addHotspotPlan($client, $name, $sharedusers, $rate)
    {
        $query = new Query('/ip/hotspot/user/profile/add');
        $query->equal('name', $name)
              ->equal('shared-users', $sharedusers)
              ->equal('rate-limit', $rate);
              
        $client->query($query)->read();
        return true;
    }

    /**
     * Set/Update a hotspot plan exactly like PHPNuxBill
     */
    private function setHotspotPlan($client, $name, $sharedusers, $rate)
    {
        // Check if profile exists
        $query = new Query('/ip/hotspot/user/profile/print');
        $query->where('name', $name);
        $profile = $client->query($query)->read();
        
        if (empty($profile)) {
            return $this->addHotspotPlan($client, $name, $sharedusers, $rate);
        }

        // Update existing profile
        $query = new Query('/ip/hotspot/user/profile/set');
        $query->equal('.id', $profile[0]['.id'])
              ->equal('shared-users', $sharedusers)
              ->equal('rate-limit', $rate);
              
        $client->query($query)->read();
        return true;
    }

    /**
     * Sync plan using PHPNuxBill's exact implementation
     */
    public function syncPlan(InternetPlan $plan)
    {
        try {
            // Connect exactly like PHPNuxBill
            $client = $this->connect($plan->router);

            // Format name exactly like PHPNuxBill - only alphanumeric
            $name = preg_replace('/[^a-zA-Z0-9]/', '', $plan->name);

            // Get rate limit from bandwidth relationship
            $rate = $plan->getRateLimit();

            $this->debug("Syncing plan to router", [
                'plan_name' => $name,
                'rate_limit' => $rate,
                'shared_users' => $plan->simultaneous_sessions
            ]);

            // Set plan exactly like PHPNuxBill
            return $this->setHotspotPlan(
                $client,
                $name,
                $plan->simultaneous_sessions,
                $rate
            );

        } catch (Exception $e) {
            Log::error('Plan sync failed', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Remove a hotspot plan exactly like PHPNuxBill
     */
    public function removePlan(InternetPlan $plan)
    {
        try {
            $client = $this->connect($plan->router);
            
            // Format name exactly like when creating - only alphanumeric
            $name = preg_replace('/[^a-zA-Z0-9]/', '', $plan->name);
            
            // Find profile
            $query = new Query('/ip/hotspot/user/profile/print');
            $query->where('name', $name);
            $profile = $client->query($query)->read();

            if (!empty($profile)) {
                $removeQuery = new Query('/ip/hotspot/user/profile/remove');
                $removeQuery->equal('.id', $profile[0]['.id']);
                $client->query($removeQuery)->read();
            }

            return true;

        } catch (Exception $e) {
            Log::error('Failed to remove plan', [
                'plan_id' => $plan->id,
                'plan_name' => $name ?? $plan->name,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Add a hotspot user
     */
    public function addHotspotUser(Router $router, string $username, string $password, InternetPlan $plan, string $mac = null, string $ip = null): array
    {
        try {
            $client = $this->connect($router);
            if (!$client) {
                return [
                    'success' => false,
                    'message' => 'Could not connect to router'
                ];
            }

            // First check if user already exists
            $existingUser = $this->getHotspotUser($router, $username);
            if ($existingUser) {
                // Remove existing user first
                $this->removeHotspotUser($router, $username);
            }

            // Prepare user profile based on plan
            $profile = $this->formatPlanName($plan->name);
            
            // Add user to hotspot users
            $client->query('/ip/hotspot/user/add', [
                'name' => $username,
                'password' => $password,
                'profile' => $profile,
                'limit-uptime' => $plan->time_limit ? $plan->time_limit . ':00:00' : '00:00:00',
                'limit-bytes-total' => $plan->data_limit ? ($plan->data_limit * 1024 * 1024) : '0',
                'mac-address' => $mac ?: '',
                'address' => $ip ?: ''
            ]);

            return [
                'success' => true,
                'message' => 'Hotspot user added successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to add hotspot user', [
                'error' => $e->getMessage(),
                'router' => $router->name,
                'username' => $username
            ]);

            return [
                'success' => false,
                'message' => 'Failed to add hotspot user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get active sessions
     */
    public function getActiveSessions()
    {
        try {
            $client = $this->connect($this->router);
            $response = $client->query('/ip/hotspot/active/print')->read();

            $sessions = [];
            foreach ($response as $session) {
                $sessions[] = [
                    'customer_id' => null, // Need to map this based on username
                    'router_id' => $this->router->id,
                    'session_id' => $session['.id'],
                    'username' => $session['user'] ?? null,
                    'ip_address' => $session['address'] ?? null,
                    'mac_address' => $session['mac-address'] ?? null,
                    'uptime' => $session['uptime'] ?? null,
                    'bytes_in' => $session['bytes-in'] ?? 0,
                    'bytes_out' => $session['bytes-out'] ?? 0
                ];
            }

            return $sessions;
        } catch (Exception $e) {
            Log::error('Failed to get active sessions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Apply bandwidth
     */
    public function applyBandwidth($router, $bandwidth, $name)
    {
        try {
            $client = $this->connect($router);
            
            $rateLimit = $this->formatBandwidth($bandwidth->rate_up, $bandwidth->rate_up_unit) . '/' . 
                        $this->formatBandwidth($bandwidth->rate_down, $bandwidth->rate_down_unit);
            
            $query = new Query('/ip/hotspot/user/profile/set');
            $query->equal('.id', $name)
                  ->equal('rate-limit', $rateLimit);
            
            if ($bandwidth->burst_limit) {
                $query->equal('burst-limit', $bandwidth->burst_limit);
            }
            if ($bandwidth->burst_threshold) {
                $query->equal('burst-threshold', $bandwidth->burst_threshold);
            }
            if ($bandwidth->burst_time) {
                $query->equal('burst-time', $bandwidth->burst_time);
            }

            $client->query($query)->read();
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to apply bandwidth', [
                'router' => $router->name,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Add a hotspot user with plan profile
     */
    public function addHotspotUserWithPlan(Router $router, string $username, string $password, InternetPlan $plan)
    {
        try {
            $client = $this->connect($router);

            // Format plan name - only alphanumeric
            $profileName = preg_replace('/[^a-zA-Z0-9]/', '', $plan->name);

            // Check if profile exists
            $query = new Query('/ip/hotspot/user/profile/print');
            $query->where('name', $profileName);
            $profile = $client->query($query)->read();

            if (empty($profile)) {
                // Create profile
                $query = new Query('/ip/hotspot/user/profile/add');
                $query->equal('name', $profileName)
                      ->equal('shared-users', $plan->shared_users ?? 1);
                
                if ($plan->bandwidth) {
                    $query->equal('rate-limit', $plan->bandwidth->speed_down . '/' . $plan->bandwidth->speed_up);
                }
                
                $client->query($query)->read();
            }

            // Remove existing user if exists
            $query = new Query('/ip/hotspot/user/print');
            $query->where('name', $username);
            $user = $client->query($query)->read();
            
            if (!empty($user)) {
                $query = new Query('/ip/hotspot/user/remove');
                $query->equal('.id', $user[0]['.id']);
                $client->query($query)->read();
            }

            // Add user with plan profile
            $query = new Query('/ip/hotspot/user/add');
            $query->equal('name', $username)
                  ->equal('password', $password)
                  ->equal('profile', $profileName);
                  
            if ($plan->type === 'time' && $plan->validity > 0) {
                $query->equal('limit-uptime', $plan->validity . 'm');
            } else {
                $query->equal('limit-uptime', '0');
            }
            
            if ($plan->data_limit > 0) {
                $query->equal('limit-bytes-total', $plan->data_limit);
            } else {
                $query->equal('limit-bytes-total', '0');
            }
            
            $client->query($query)->read();

            return [
                'success' => true,
                'message' => 'User added successfully'
            ];

        } catch (Exception $e) {
            Log::error('Failed to add hotspot user', [
                'username' => $username,
                'router' => $router->name,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Failed to add user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove active session for a user
     */
    public function removeActiveSession($router, $username)
    {
        try {
            $client = $this->connect($router);

            // Find active session
            $query = new Query('/ip/hotspot/active/print');
            $query->where('user', $username);
            $printRequest = $client->query($query)->read();

            foreach ($printRequest as $session) {
                $query = new Query('/ip/hotspot/active/remove');
                $query->equal('.id', $session['.id']);
                $client->query($query)->read();
            }

            return [
                'success' => true,
                'message' => 'Active sessions removed'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to remove active session', [
                'username' => $username,
                'router' => $router->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to remove session: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get client MAC address from Mikrotik
     */
    public function getClientMacAddress(Router $router, string $ipAddress)
    {
        try {
            $client = $this->connect($router);

            // First check in active list
            $query = new Query('/ip/hotspot/active/print');
            $query->where('address', $ipAddress);
            $request = $client->query($query)->read();

            if (count($request) > 0) {
                return $request[0]['mac-address'];
            }

            // If not found in active, check in host list
            $query = new Query('/ip/hotspot/host/print');
            $query->where('address', $ipAddress);
            $request = $client->query($query)->read();

            if (count($request) > 0) {
                return $request[0]['mac-address'];
            }

            // If not found in host, check in arp list
            $query = new Query('/ip/arp/print');
            $query->where('address', $ipAddress);
            $request = $client->query($query)->read();

            if (count($request) > 0) {
                return $request[0]['mac-address'];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to get MAC address', [
                'ip' => $ipAddress,
                'router' => $router->name,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Login a hotspot user
     */
    public function loginHotspotUser(Router $router, $username, $password, $macAddress, $ipAddress)
    {
        try {
            $client = $this->connect($router);

            // First check if user exists
            $query = new Query('/ip/hotspot/user/print');
            $query->where('name', $username);
            $printRequest = $client->query($query)->read();

            if (count($printRequest) === 0) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Remove any existing active sessions for this MAC
            $query = new Query('/ip/hotspot/active/print');
            $query->where('mac-address', $macAddress);
            $activeRequest = $client->query($query)->read();
            
            if (count($activeRequest) > 0) {
                $query = new Query('/ip/hotspot/active/remove');
                $query->equal('.id', $activeRequest[0]['.id']);
                $client->query($query)->read();
            }

            // Login using /ip/hotspot/active/login
            $query = new Query('/ip/hotspot/active/login');
            $query->equal('user', $username)
                  ->equal('password', $password)
                  ->equal('mac-address', $macAddress)
                  ->equal('ip', $ipAddress);
                  
            $client->query($query)->read();

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Failed to login hotspot user', [
                'username' => $username,
                'router' => $router->name,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get hotspot user information
     */
    public function getHotspotUserInfo(Router $router, $username)
    {
        try {
            $client = $this->connect($router);

            // Get user details
            $query = new Query('/ip/hotspot/user/print');
            $query->where('name', $username);
            $user = $client->query($query)->read();

            if (empty($user)) {
                return null;
            }

            // Get active session
            $query = new Query('/ip/hotspot/active/print');
            $query->where('user', $username);
            $active = $client->query($query)->read();

            return [
                'profile' => $user[0]['profile'],
                'uptime' => !empty($active) ? $active[0]['uptime'] : '0s',
                'bytes_in' => !empty($active) ? $active[0]['bytes-in'] : '0',
                'bytes_out' => !empty($active) ? $active[0]['bytes-out'] : '0',
                'time_left' => $user[0]['limit-uptime'] ?? 'Unlimited',
                'data_left' => $user[0]['limit-bytes-total'] ?? 'Unlimited',
                'expires_at' => isset($user[0]['limit-uptime']) 
                    ? now()->addSeconds(strtotime($user[0]['limit-uptime'])) 
                    : null
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get hotspot user info', [
                'username' => $username,
                'router' => $router->name,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Logout a hotspot user
     */
    public function logoutHotspotUser(Router $router, $username, $ipAddress)
    {
        try {
            $client = $this->connect($router);

            // Find active session
            $query = new Query('/ip/hotspot/active/print');
            $query->where('user', $username)
                  ->where('address', $ipAddress);
            $printRequest = $client->query($query)->read();

            if (count($printRequest) === 0) {
                return true; // Already logged out
            }

            // Get the session ID
            $sessionId = $printRequest[0]['.id'];

            // Remove the active session
            $query = new Query('/ip/hotspot/active/remove');
            $query->equal('.id', $sessionId);
            $client->query($query)->read();

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to logout hotspot user', [
                'username' => $username,
                'router' => $router->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to remove session: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Debug logging helper
     */
    private function debug($message, $context = [], $level = 'debug')
    {
        $context['timestamp'] = now()->toDateTimeString();
        $context['memory_usage'] = memory_get_usage(true);
        
        switch ($level) {
            case 'error':
                Log::error($message, $context);
                break;
            case 'warning':
                Log::warning($message, $context);
                break;
            case 'info':
                Log::info($message, $context);
                break;
            default:
                Log::debug($message, $context);
        }
    }

    /**
     * Get active hotspot user by MAC address
     */
    public function getActiveHotspotUser(Router $router, string $mac): ?array
    {
        try {
            $client = $this->connect($router);
            if (!$client) {
                return null;
            }

            $response = $client->query('/ip/hotspot/active/print', [
                '?mac-address' => $mac
            ]);

            return !empty($response) ? (array)$response[0] : null;

        } catch (\Exception $e) {
            Log::error('Failed to get active hotspot user', [
                'error' => $e->getMessage(),
                'router' => $router->name,
                'mac' => $mac
            ]);
            return null;
        }
    }

    /**
     * Get a hotspot user by username
     */
    public function getHotspotUser(Router $router, string $username): ?array
    {
        try {
            $client = $this->connect($router);
            if (!$client) {
                return null;
            }

            $response = $client->query('/ip/hotspot/user/print', [
                '?name' => $username
            ]);

            return !empty($response) ? (array)$response[0] : null;

        } catch (\Exception $e) {
            Log::error('Failed to get hotspot user', [
                'error' => $e->getMessage(),
                'router' => $router->name,
                'username' => $username
            ]);
            return null;
        }
    }

    /**
     * Remove a hotspot user
     */
    public function removeHotspotUser(Router $router, string $username): bool
    {
        try {
            $client = $this->connect($router);
            if (!$client) {
                return false;
            }

            // First get the user ID
            $user = $this->getHotspotUser($router, $username);
            if (!$user || !isset($user['.id'])) {
                return false;
            }

            // Remove the user
            $client->query('/ip/hotspot/user/remove', [
                '.id' => $user['.id']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to remove hotspot user', [
                'error' => $e->getMessage(),
                'router' => $router->name,
                'username' => $username
            ]);
            return false;
        }
    }

    /**
     * Upload a file to the router
     */
    public function uploadFile(Router $router, string $localPath, string $remotePath): array
    {
        try {
            $client = $this->connect($router);
            if (!$client) {
                return [
                    'success' => false,
                    'message' => 'Could not connect to router'
                ];
            }

            // Create the remote directory if it doesn't exist
            $client->query('/file/print', [
                '?name' => $remotePath
            ]);
            
            // Upload the file
            $client->query('/tool/fetch', [
                'url' => 'file://' . $localPath,
                'dst-path' => $remotePath,
                'upload' => true
            ]);

            return [
                'success' => true,
                'message' => 'File uploaded successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to upload file to router', [
                'error' => $e->getMessage(),
                'router' => $router->name,
                'localPath' => $localPath,
                'remotePath' => $remotePath
            ]);

            return [
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload a directory recursively to the router
     */
    public function uploadDirectory(Router $router, string $localDir, string $remoteDir): array
    {
        try {
            if (!is_dir($localDir)) {
                return [
                    'success' => false,
                    'message' => 'Local directory does not exist'
                ];
            }

            $client = $this->connect($router);
            if (!$client) {
                return [
                    'success' => false,
                    'message' => 'Could not connect to router'
                ];
            }

            // Create the remote directory
            $client->query('/file/print', [
                '?name' => $remoteDir
            ]);

            // Recursively upload all files
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($localDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $localPath = str_replace('\\', '/', $item->getPathname());
                $relativePath = substr($localPath, strlen($localDir));
                $remotePath = $remoteDir . $relativePath;

                if ($item->isDir()) {
                    // Create directory on router
                    $client->query('/file/print', [
                        '?name' => $remotePath
                    ]);
                } else {
                    // Upload file
                    $this->uploadFile($router, $localPath, $remotePath);
                }
            }

            return [
                'success' => true,
                'message' => 'Directory uploaded successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to upload directory to router', [
                'error' => $e->getMessage(),
                'router' => $router->name,
                'localDir' => $localDir,
                'remoteDir' => $remoteDir
            ]);

            return [
                'success' => false,
                'message' => 'Failed to upload directory: ' . $e->getMessage()
            ];
        }
    }
}