<?php

namespace App\Models;

use PEAR2\Net\RouterOS\Client;
use PEAR2\Net\RouterOS\Query;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Services\MikrotikService;
use Illuminate\Database\Eloquent\SoftDeletes;

class Router extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'ip',
        'username',
        'password',
        'description',
        'is_active',
        'is_online',
        'last_seen'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'last_seen' => 'datetime'
    ];

    /**
     * Add default values
     */
    protected $attributes = [
        'username' => 'admin',
        'is_active' => true
    ];

    /**
     * Get all active sessions for this router
     */
    public function activeSessions()
    {
        return $this->hasMany(ActiveSession::class);
    }

    /**
     * Get the connection address (hostname or IP)
     */
    public function getConnectionAddress()
    {
        return !empty($this->hostname) ? $this->hostname : $this->ip;
    }

    /**
     * Get RouterOS client instance
     */
    public function getClient()
    {
        try {
            // Parse connection address and port
            $address = $this->getConnectionAddress();
            $iport = explode(":", $address);
            $host = trim($iport[0]); // Hostname or IP address
            
            // Validate host
            if (empty($host)) {
                throw new Exception('Invalid hostname or IP address');
            }
            
            // Get port, defaulting to 8728 if not specified
            $port = isset($iport[1]) ? (int)$iport[1] : 8728;
            
            // Validate port number
            if ($port <= 0 || $port > 65535) {
                throw new Exception('Invalid port number. Port must be between 1 and 65535');
            }
            
            // Create client instance
            $client = new Client(
                $host,
                (string)$this->api_username,
                (string)$this->api_password,
                $port
            );

            return $client;
        } catch (Exception $e) {
            Log::error('Failed to create RouterOS client: ' . $e->getMessage(), [
                'router_id' => $this->id,
                'hostname' => $this->hostname,
                'ip_address' => $this->ip,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Could not connect to router. Please verify hostname/IP and port are correct: ' . $e->getMessage());
        }
    }

    /**
     * Test connection to the router
     */
    public function testConnection()
    {
        try {
            $service = app(MikrotikService::class);
            $client = $service->connect($this);
            
            // Get system resources
            $query = new Query('/system/resource/print');
            $response = $client->query($query)->read();
            
            return [
                'success' => true,
                'data' => $response[0] ?? []
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if the router is online
     */
    public function isOnline()
    {
        return $this->status === 'online';
    }

    /**
     * Get the router status badge class
     */
    public function getStatusBadgeClass()
    {
        if ($this->status === 'online') {
            return 'success';
        } elseif ($this->status === 'offline') {
            return 'danger';
        }
        return 'warning';
    }

    /**
     * Get the router status text
     */
    public function getStatusText()
    {
        if ($this->status === 'online') {
            return 'Online';
        } elseif ($this->status === 'offline') {
            return 'Offline';
        }
        return 'Unknown';
    }

    /**
     * Update the router status
     */
    public function updateStatus($isOnline)
    {
        $now = now();
        
        // Update status and timestamps
        $this->update([
            'status' => $isOnline ? 'online' : 'offline',
            'last_check' => $now,
            'last_seen' => $isOnline ? $now : $this->last_seen
        ]);

        // Update the model instance
        $this->status = $isOnline ? 'online' : 'offline';
        $this->last_check = $now;
        if ($isOnline) {
            $this->last_seen = $now;
        }

        // Clear any cached status
        $this->refresh();

        // Return updated status info
        return [
            'status' => $this->getStatusText(),
            'statusClass' => $this->getStatusBadgeClass(),
            'lastSeen' => $this->getLastSeenHuman()
        ];
    }

    /**
     * Get the last seen time in human readable format
     */
    public function getLastSeenHuman()
    {
        if (!$this->last_seen) {
            return 'Never';
        }
        return $this->last_seen->diffForHumans();
    }

    /**
     * Get connection details exactly like PHPNuxBill
     */
    public function getConnectionDetails()
    {
        return [
            'ip' => $this->ip,
            'username' => $this->username,
            'password' => $this->password
        ];
    }

    /**
     * Get plans associated with this router
     */
    public function plans()
    {
        return $this->hasMany(InternetPlan::class);
    }

    /**
     * Get the port number from the IP address
     */
    public function getPortAttribute()
    {
        $parts = explode(':', $this->ip);
        return isset($parts[1]) ? intval($parts[1]) : 8728;
    }

    /**
     * Get just the IP part of the address
     */
    public function getHostAttribute()
    {
        $parts = explode(':', $this->ip);
        return $parts[0];
    }

    /**
     * Set the IP address, ensuring it has a port
     */
    public function setIpAddressAttribute($value)
    {
        if (strpos($value, ':') === false) {
            $value .= ':8728';
        }
        $this->attributes['ip'] = $value;
    }

    /**
     * Set the API password with encryption
     */
    public function setApiPasswordAttribute($value)
    {
        $this->attributes['api_password'] = encrypt($value);
    }

    /**
     * Get the decrypted API password
     */
    public function getApiPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (Exception $e) {
            return $value;
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }
}