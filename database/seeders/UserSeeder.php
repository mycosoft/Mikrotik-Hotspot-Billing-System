<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing users
        DB::table('users')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();

        // Create roles if they don't exist
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $operatorRole = Role::firstOrCreate(['name' => 'Operator', 'guard_name' => 'web']);

        // Create default permissions
        $permissions = [
            'view dashboard',
            'view customers',
            'manage customers',
            'view vouchers',
            'manage vouchers',
            'view plans',
            'manage plans',
            'view bandwidth profiles',
            'manage bandwidth profiles',
            'view bandwidths',
            'manage bandwidths',
            'view routers',
            'manage routers',
            'view sessions',
            'manage sessions',
            'view reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }

        // Super Admin (Full Access)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('SuperAdmin2025!'),
            'remember_token' => Str::random(10),
        ]);
        $superAdmin->assignRole($superAdminRole);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin
        $admin = User::create([
            'name' => 'Network Admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Admin2025!'),
            'remember_token' => Str::random(10),
        ]);
        $admin->assignRole($adminRole);
        $admin->givePermissionTo([
            'view dashboard',
            'view customers',
            'manage customers',
            'view vouchers',
            'manage vouchers',
            'view plans',
            'manage plans',
            'view bandwidth profiles',
            'manage bandwidth profiles',
            'view bandwidths',
            'manage bandwidths',
            'view routers',
            'manage routers',
            'view sessions',
            'manage sessions',
            'view reports',
        ]);

        // Manager
        $manager = User::create([
            'name' => 'Network Manager',
            'email' => 'manager@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Manager2025!'),
            'remember_token' => Str::random(10),
        ]);
        $manager->assignRole($managerRole);
        $manager->givePermissionTo([
            'view dashboard',
            'view customers',
            'view vouchers',
            'view plans',
            'view bandwidth profiles',
            'view bandwidths',
            'view routers',
            'view sessions',
            'view reports',
        ]);

        // Operator
        $operator = User::create([
            'name' => 'Network Operator',
            'email' => 'operator@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Operator2025!'),
            'remember_token' => Str::random(10),
        ]);
        $operator->assignRole($operatorRole);
        $operator->givePermissionTo([
            'view dashboard',
            'view customers',
            'view vouchers',
            'view sessions',
        ]);
    }
}
