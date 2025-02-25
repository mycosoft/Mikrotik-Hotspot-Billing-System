<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Router;

class RouterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routers = [
            [
                'name' => 'Main Router',
                'ip_address' => '192.168.1.1',
                'username' => 'admin',
                'password' => 'password123', // In production, use encrypted passwords
                'port' => 8728,
                'api_username' => null,
                'api_password' => null,
                'use_ssl' => false,
                'description' => 'Main router for hotspot services',
                'is_active' => true,
            ],
            [
                'name' => 'Backup Router',
                'ip_address' => '192.168.1.2',
                'username' => 'admin',
                'password' => 'password123', // In production, use encrypted passwords
                'port' => 8728,
                'api_username' => null,
                'api_password' => null,
                'use_ssl' => false,
                'description' => 'Backup router for failover',
                'is_active' => true,
            ],
        ];

        foreach ($routers as $router) {
            Router::create($router);
        }
    }
}
