<?php

namespace App\Models;

use App\Models\Bandwidth;
use App\Models\Router;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternetPlan extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'limit_type',
        'time_limit',
        'data_limit',
        'price',
        'validity_days',
        'bandwidth_id',
        'router_id',
        'simultaneous_sessions',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'time_limit' => 'integer',
        'data_limit' => 'integer',
        'price' => 'float',
        'validity_days' => 'integer',
        'simultaneous_sessions' => 'integer'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'activeVoucherCount'
    ];

    /**
     * Get the bandwidth for this plan
     */
    public function bandwidth(): BelongsTo
    {
        return $this->belongsTo(Bandwidth::class);
    }

    /**
     * Get the router for this plan
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    /**
     * Get all vouchers for this plan
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'plan_id');
    }

    /**
     * Get the rate limit string for Mikrotik
     */
    public function getRateLimit(): string
    {
        if (!$this->bandwidth) {
            return '0/0';
        }
        return $this->bandwidth->rate_limit;
    }

    /**
     * Get count of active vouchers
     */
    public function getActiveVoucherCountAttribute(): int
    {
        return $this->vouchers()
            ->where('is_used', true)
            ->where('expires_at', '>', now())
            ->count();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($plan) {
            // Delete associated vouchers when plan is deleted
            $plan->vouchers()->delete();
        });
    }
}
