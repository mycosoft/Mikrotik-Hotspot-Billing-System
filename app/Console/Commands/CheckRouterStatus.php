<?php

namespace App\Console\Commands;

use App\Services\RouterService;
use Illuminate\Console\Command;

class CheckRouterStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'router:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check online status of all active routers';

    /**
     * Execute the console command.
     */
    public function handle(RouterService $routerService)
    {
        $this->info('Checking router status...');
        
        try {
            $routerService->checkAllRoutersStatus();
            $this->info('Router status check completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error checking router status: ' . $e->getMessage());
        }
    }
}
