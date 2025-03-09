<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bandwidth extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'rate_up',
        'rate_up_unit',
        'rate_down',
        'rate_down_unit',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'rate_up' => 'integer',
        'rate_down' => 'integer',
    ];

    /**
     * Get the internet plans associated with this bandwidth
     */
    public function internetPlans()
    {
        return $this->hasMany(InternetPlan::class, 'bandwidth_id');
    }

    /**
     * Scope a query to only include active bandwidths
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by bandwidth type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the formatted rate limit string for RouterOS
     * Format: {up}M/{down}M for Mikrotik hotspot profiles
     */
    public function getRateLimitAttribute()
    {
        $up = $this->rate_up;
        $down = $this->rate_down;

        // Use k for Kbps and M for Mbps
        $up_unit = $this->rate_up_unit === 'Kbps' ? 'k' : 'M';
        $down_unit = $this->rate_down_unit === 'Kbps' ? 'k' : 'M';

        // Format like other profiles: upload/download
        return "{$up}{$up_unit}/{$down}{$down_unit}";
    }

    /**
     * Get the formatted speed for display in UI
     */
    public function getSpeedAttribute()
    {
        return "{$this->rate_down}{$this->rate_down_unit}/{$this->rate_up}{$this->rate_up_unit}";
    }

    /**
     * Convert speed to bits for RouterOS based on unit
     */
    protected function convertToBits($rate, $unit)
    {
        if ($unit === 'Kbps') {
            return $rate * 1024;
        } else {
            return $rate * 1048576; // Mbps
        }
    }

    /**
     * Check if the bandwidth is within specified limits
     */
    public function withinLimits($uploadUsed, $downloadUsed)
    {
        return $uploadUsed <= $this->convertToBits($this->rate_up, $this->rate_up_unit) && 
               $downloadUsed <= $this->convertToBits($this->rate_down, $this->rate_down_unit);
    }

    /**
     * Get the number of associated active internet plans
     */
    public function getActivePlanCountAttribute()
    {
        return $this->internetPlans()->where('is_active', true)->count();
    }

    /**
     * Apply this bandwidth profile to a router
     */
    public function applyToRouter(Router $router, $profileName)
    {
        try {
            $client = $router->getClient();
            
            // Check if it's a hotspot or pppoe profile
            if (str_starts_with($profileName, 'hs-')) {
                // Hotspot profile
                $this->applyHotspotProfile($client, $profileName);
            } else {
                // PPPoE profile
                $this->applyPppoeProfile($client, $profileName);
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to apply bandwidth profile: ' . $e->getMessage(), [
                'router_id' => $router->id,
                'bandwidth_id' => $this->id,
                'profile_name' => $profileName
            ]);
            return false;
        }
    }

    /**
     * Apply bandwidth settings to a hotspot profile
     */
    protected function applyHotspotProfile($client, $profileName)
    {
        // Check if profile exists
        $printRequest = new \PEAR2\Net\RouterOS\Request(
            '/ip hotspot user profile print .proplist=.id',
            \PEAR2\Net\RouterOS\Query::where('name', $profileName)
        );
        $profileId = $client->sendSync($printRequest)->getProperty('.id');
        
        if (empty($profileId)) {
            // Create new profile
            $addRequest = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/profile/add');
            $client->sendSync(
                $addRequest
                    ->setArgument('name', $profileName)
                    ->setArgument('rate-limit', $this->rate_limit)
            );
        } else {
            // Update existing profile
            $setRequest = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/profile/set');
            $client->sendSync(
                $setRequest
                    ->setArgument('numbers', $profileId)
                    ->setArgument('rate-limit', $this->rate_limit)
            );
        }
    }

    /**
     * Apply bandwidth settings to a PPPoE profile
     */
    protected function applyPppoeProfile($client, $profileName)
    {
        // Check if profile exists
        $printRequest = new \PEAR2\Net\RouterOS\Request(
            '/ppp profile print .proplist=.id',
            \PEAR2\Net\RouterOS\Query::where('name', $profileName)
        );
        $profileId = $client->sendSync($printRequest)->getProperty('.id');
        
        if (empty($profileId)) {
            // Create new profile
            $addRequest = new \PEAR2\Net\RouterOS\Request('/ppp/profile/add');
            $client->sendSync(
                $addRequest
                    ->setArgument('name', $profileName)
                    ->setArgument('rate-limit', $this->rate_limit)
            );
        } else {
            // Update existing profile
            $setRequest = new \PEAR2\Net\RouterOS\Request('/ppp/profile/set');
            $client->sendSync(
                $setRequest
                    ->setArgument('numbers', $profileId)
                    ->setArgument('rate-limit', $this->rate_limit)
            );
        }
    }

    /**
     * Parse a RouterOS rate limit string into upload/download speeds
     * Format: {upload}M/{download}M
     */
    public static function parseRateLimit($rateLimit)
    {
        if (empty($rateLimit)) {
            return [0, 0];
        }

        $parts = explode('/', $rateLimit);
        if (count($parts) !== 2) {
            return [0, 0];
        }

        $upload = trim($parts[0]);
        $download = trim($parts[1]);

        // Convert to Kbps
        $upload = self::convertToKbps($upload);
        $download = self::convertToKbps($download);

        return [$upload, $download];
    }

    /**
     * Convert a RouterOS bandwidth value to Kbps
     * Examples: 1M = 1024, 512k = 512
     */
    protected static function convertToKbps($value)
    {
        $value = strtolower(trim($value));
        $value = rtrim($value, 'kbps');
        
        if (str_ends_with($value, 'm')) {
            return (int)(rtrim($value, 'm') * 1024);
        }
        if (str_ends_with($value, 'k')) {
            return (int)rtrim($value, 'k');
        }
        
        return (int)$value;
    }

    // Convert Mbps to Kbps when saving
    public function setRateUpAttribute($value)
    {
        $unit = request('rate_up_unit', 'K');
        $this->attributes['rate_up'] = $unit === 'M' ? $value * 1024 : $value;
        $this->attributes['rate_up_unit'] = $unit;
    }

    public function setRateDownAttribute($value)
    {
        $unit = request('rate_down_unit', 'K');
        $this->attributes['rate_down'] = $unit === 'M' ? $value * 1024 : $value;
        $this->attributes['rate_down_unit'] = $unit;
    }

    // Convert Kbps to Mbps for display when unit is M
    public function getRateUpDisplayAttribute()
    {
        return $this->rate_up_unit === 'M' ? $this->rate_up / 1024 : $this->rate_up;
    }

    public function getRateDownDisplayAttribute()
    {
        return $this->rate_down_unit === 'M' ? $this->rate_down / 1024 : $this->rate_down;
    }

    public function getFormattedUploadSpeedAttribute()
    {
        return $this->rate_up_display . ' ' . ($this->rate_up_unit === 'M' ? 'Mbps' : 'Kbps');
    }

    public function getFormattedDownloadSpeedAttribute()
    {
        return $this->rate_down_display . ' ' . ($this->rate_down_unit === 'M' ? 'Mbps' : 'Kbps');
    }

    /**
     * Get formatted upload speed for display
     */
    public function getDisplayUploadAttribute()
    {
        return "{$this->rate_up} {$this->rate_up_unit}";
    }

    /**
     * Get formatted download speed for display
     */
    public function getDisplayDownloadAttribute()
    {
        return "{$this->rate_down} {$this->rate_down_unit}";
    }
}
