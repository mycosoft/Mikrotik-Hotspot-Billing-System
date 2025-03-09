<?php

namespace App\Models;

class ActiveSession extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'router_id',
        'session_id',
        'ip_address',
        'mac_address',
        'bytes_in',
        'bytes_out',
        'uptime_seconds',
        'started_at',
        'expires_at',
        'session_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'uptime_seconds' => 'integer',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'session_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the session
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the router that this session is connected to
     */
    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    /**
     * Get formatted bytes in
     */
    public function getFormattedBytesInAttribute()
    {
        return $this->formatBytes($this->bytes_in);
    }

    /**
     * Get formatted bytes out
     */
    public function getFormattedBytesOutAttribute()
    {
        return $this->formatBytes($this->bytes_out);
    }

    /**
     * Get formatted uptime
     */
    public function getFormattedUptimeAttribute()
    {
        $seconds = $this->uptime_seconds;
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        $parts = [];
        if ($days > 0) $parts[] = "{$days}d";
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";
        if ($seconds > 0) $parts[] = "{$seconds}s";

        return implode(' ', $parts);
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
