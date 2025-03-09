<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsSettings extends Model
{
    protected $fillable = [
        'provider',
        'api_key',
        'api_secret',
        'sender_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
} 