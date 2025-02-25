<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bandwidth;

class BandwidthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bandwidths = [
            [
                'name' => '1Mbps Basic',
                'rate_up' => 1,
                'rate_up_unit' => 'Mbps',
                'rate_down' => 1,
                'rate_down_unit' => 'Mbps',
                'burst_limit' => '2M/2M',
                'burst_threshold' => '1M/1M',
                'burst_time' => '10/10',
            ],
            [
                'name' => '2Mbps Standard',
                'rate_up' => 2,
                'rate_up_unit' => 'Mbps',
                'rate_down' => 2,
                'rate_down_unit' => 'Mbps',
                'burst_limit' => '4M/4M',
                'burst_threshold' => '2M/2M',
                'burst_time' => '10/10',
            ],
            [
                'name' => '5Mbps Premium',
                'rate_up' => 5,
                'rate_up_unit' => 'Mbps',
                'rate_down' => 5,
                'rate_down_unit' => 'Mbps',
                'burst_limit' => '10M/10M',
                'burst_threshold' => '5M/5M',
                'burst_time' => '10/10',
            ],
        ];

        foreach ($bandwidths as $bandwidth) {
            Bandwidth::create($bandwidth);
        }
    }
}
