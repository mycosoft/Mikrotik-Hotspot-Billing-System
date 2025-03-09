<?php

namespace App\Models;

class Customer extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'username',
        'password',
        'balance',
        'service_type',
        'pppoe_username',
        'pppoe_password',
        'ip_address',
        'status',
        'latitude',
        'longitude',
        'is_active',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'pppoe_password',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'status' => 'string',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_login',
        'expiry_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get all active sessions for the customer
     */
    public function activeSessions()
    {
        return $this->hasMany(ActiveSession::class);
    }

    /**
     * Get all vouchers used by the customer
     */
    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'used_by');
    }

    /**
     * Get all transactions for the customer
     */
    public function transactions()
    {
        return $this->hasMany(CustomerTransaction::class);
    }
}
