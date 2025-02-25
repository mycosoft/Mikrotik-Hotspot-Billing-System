<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BandwidthProfile;

class BandwidthProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'name' => '1Mbps Basic',
                'description' => 'Basic internet speed suitable for browsing and email',
                'download_limit_kbps' => 1024,
                'upload_limit_kbps' => 512,
                'burst_limit' => '2M/1M',
                'burst_threshold' => '1M/512k',
                'burst_time' => '30s/30s',
                'queue_settings' => json_encode([
                    'priority' => 8,
                    'queue_type' => 'default-small',
                ]),
            ],
            [
                'name' => '2Mbps Standard',
                'description' => 'Standard speed for streaming and light gaming',
                'download_limit_kbps' => 2048,
                'upload_limit_kbps' => 1024,
                'burst_limit' => '4M/2M',
                'burst_threshold' => '2M/1M',
                'burst_time' => '30s/30s',
                'queue_settings' => json_encode([
                    'priority' => 5,
                    'queue_type' => 'default-small',
                ]),
            ],
            [
                'name' => '5Mbps Premium',
                'description' => 'Premium speed for HD streaming and gaming',
                'download_limit_kbps' => 5120,
                'upload_limit_kbps' => 2048,
                'burst_limit' => '8M/4M',
                'burst_threshold' => '5M/2M',
                'burst_time' => '30s/30s',
                'queue_settings' => json_encode([
                    'priority' => 3,
                    'queue_type' => 'default-small',
                ]),
            ],
        ];

        foreach ($profiles as $profile) {
            BandwidthProfile::create($profile);
        }
    }
}
