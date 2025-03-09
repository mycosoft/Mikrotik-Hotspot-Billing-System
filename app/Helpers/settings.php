<?php

use App\Models\Setting;

if (!function_exists('get_setting')) {
    function get_setting($key, $default = null) {
        try {
            return Setting::where('setting', $key)->value('value') ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}
