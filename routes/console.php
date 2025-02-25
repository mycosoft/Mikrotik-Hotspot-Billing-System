<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Custom hotspot commands
Artisan::command('hotspot:status', function () {
    $routers = \App\Models\Router::where('is_active', true)->get();
    $this->info('Hotspot System Status:');
    
    foreach ($routers as $router) {
        $this->line("\nRouter: {$router->name}");
        $this->line("Active Sessions: " . $router->activeSessions()->count());
        $this->line("Active Customers: " . $router->activeSessions()->distinct('customer_id')->count());
    }

    $totalVouchers = \App\Models\Voucher::count();
    $usedVouchers = \App\Models\Voucher::where('is_used', true)->count();
    
    $this->line("\nVoucher Statistics:");
    $this->line("Total Vouchers: {$totalVouchers}");
    $this->line("Used Vouchers: {$usedVouchers}");
    $this->line("Available Vouchers: " . ($totalVouchers - $usedVouchers));
})->purpose('Display hotspot system status');
