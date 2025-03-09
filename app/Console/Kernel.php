<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\MonitorActiveSessions::class,
        Commands\GenerateVouchers::class,
        Commands\CleanupExpiredSessions::class,
        Commands\SyncActiveSessions::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check router status every 5 minutes
        $schedule->command('router:check-status')->everyFiveMinutes();

        // Monitor active sessions every minute
        $schedule->command('hotspot:monitor-sessions')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Sync active sessions every minute
        $schedule->command('hotspot:sync-sessions')->everyMinute();

        // Cleanup expired sessions every 5 minutes
        $schedule->command('hotspot:cleanup-sessions')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Auto-generate vouchers for plans that are running low (example)
        $schedule->command('hotspot:generate-vouchers 1 --quantity=50')
            ->dailyAt('00:00')
            ->when(function () {
                // Check if any plan needs more vouchers
                return \App\Models\InternetPlan::whereHas('vouchers', function ($query) {
                    $query->where('is_used', false);
                }, '<', 10)->exists();
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
