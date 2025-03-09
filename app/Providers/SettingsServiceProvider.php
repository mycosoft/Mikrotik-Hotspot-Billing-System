<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only run after migrations
        if (Schema::hasTable('settings')) {
            try {
                $companyName = Setting::where('setting', 'company_name')->value('value');
                if ($companyName) {
                    Config::set('app.name', $companyName);
                    Config::set('adminlte.title', $companyName);
                    Config::set('adminlte.logo', $companyName);
                    Config::set('adminlte.logo_img_alt', $companyName);
                }
            } catch (\Exception $e) {
                // If database is not available yet, use default name
            }
        }
    }
}