<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'setting',
        'value',
        'description',
        'type'
    ];

    public static function get($key, $default = null)
    {
        $setting = self::where('setting', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $description = null, $type = 'text')
    {
        $setting = self::where('setting', $key)->first();
        if ($setting) {
            $setting->value = $value;
            if ($description) {
                $setting->description = $description;
            }
            $setting->save();
        } else {
            self::create([
                'setting' => $key,
                'value' => $value,
                'description' => $description,
                'type' => $type
            ]);
        }
    }
}
