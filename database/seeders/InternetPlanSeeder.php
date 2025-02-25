<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InternetPlan;
use App\Models\Router;
use App\Models\Bandwidth;

class InternetPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainRouter = Router::where('name', 'Main Router')->first();
        $basicBandwidth = Bandwidth::where('name', '1Mbps Basic')->first();
        $standardBandwidth = Bandwidth::where('name', '2Mbps Standard')->first();
        $premiumBandwidth = Bandwidth::where('name', '5Mbps Premium')->first();

        $plans = [
            [
                'name' => 'Daily Basic',
                'type' => 'limited',
                'limit_type' => 'time',
                'time_limit' => 1440, // 24 hours in minutes
                'data_limit' => null,
                'price' => 2000, // UGX
                'validity_days' => 1,
                'router_id' => $mainRouter->id,
                'bandwidth_id' => $basicBandwidth->id,
                'simultaneous_sessions' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Weekly Standard',
                'type' => 'limited',
                'limit_type' => 'both',
                'time_limit' => 10080, // 7 days in minutes
                'data_limit' => 10240, // 10 GB in MB
                'price' => 10000, // UGX
                'validity_days' => 7,
                'router_id' => $mainRouter->id,
                'bandwidth_id' => $standardBandwidth->id,
                'simultaneous_sessions' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Monthly Premium',
                'type' => 'unlimited',
                'limit_type' => null,
                'time_limit' => null,
                'data_limit' => null,
                'price' => 50000, // UGX
                'validity_days' => 30,
                'router_id' => $mainRouter->id,
                'bandwidth_id' => $premiumBandwidth->id,
                'simultaneous_sessions' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Daily Data Pack',
                'type' => 'limited',
                'limit_type' => 'data',
                'time_limit' => null,
                'data_limit' => 1024, // 1 GB in MB
                'price' => 3000, // UGX
                'validity_days' => 1,
                'router_id' => $mainRouter->id,
                'bandwidth_id' => $standardBandwidth->id,
                'simultaneous_sessions' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            InternetPlan::create($plan);
        }
    }
}
