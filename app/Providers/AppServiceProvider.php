<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Set company name from settings
        if (Schema::hasTable('settings')) {
            try {
                $companyName = Setting::where('setting', 'company_name')->value('value');
                if ($companyName) {
                    Config::set('app.name', $companyName);
                }
            } catch (\Exception $e) {
                // If database is not available yet, use default name
            }
        }
    }
}
