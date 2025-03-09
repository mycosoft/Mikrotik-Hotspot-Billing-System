<?php

namespace App\Models;

use App\Models\User;
use App\Models\Customer;
use App\Models\InternetPlan;
use App\Models\Router;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'routers',
        'plan_id',
        'code',
        'status',
        'is_used',
        'used_at',
        'used_by',
        'generated_by',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the internet plan associated with the voucher
     */
    public function plan()
    {
        return $this->belongsTo(InternetPlan::class, 'plan_id');
    }

    /**
     * Get the customer who used this voucher
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'used_by');
    }

    /**
     * Get the user who generated this voucher
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the router associated with this voucher
     */
    public function router()
    {
        return $this->belongsTo(Router::class, 'routers', 'id');
    }

    /**
     * Check if the voucher is expired
     */
    public function isExpired()
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    /**
     * Check if the voucher is valid (not used and not expired)
     */
    public function isValid()
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Scope a query to only include unused vouchers
     */
    public function scopeUnused($query)
    {
        return $query->where('is_used', false);
    }

    /**
     * Scope a query to only include used vouchers
     */
    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    /**
     * Scope a query to only include vouchers for a specific type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Activate the voucher
     */
    public function activate(Customer $customer)
    {
        if (!$this->isValid()) {
            throw new \Exception('Voucher is not valid for activation.');
        }

        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'used_by' => $customer->id,
            'expires_at' => now()->addDays($this->plan->validity_days),
        ]);

        return $this;
    }

    /**
     * Generate a unique voucher code
     */
    public static function generateUniqueCode($format = 'numbers', $prefix = '', $length = 12)
    {
        do {
            $code = self::generateCode($format, $prefix, $length);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Internal method to generate voucher code
     */
    private static function generateCode($format, $prefix = '', $length = 12)
    {
        $prefix = $prefix ?: '';
        $baseLength = $length - strlen($prefix);

        switch ($format) {
            case 'numbers':
                $code = $prefix . str_pad(mt_rand(0, pow(10, $baseLength) - 1), $baseLength, '0', STR_PAD_LEFT);
                break;
            case 'up':
                $code = $prefix . strtoupper(Str::random($baseLength));
                break;
            case 'low':
                $code = $prefix . strtolower(Str::random($baseLength));
                break;
            case 'rand':
            default:
                $code = $prefix . Str::random($baseLength);
        }

        return $code;
    }
}
