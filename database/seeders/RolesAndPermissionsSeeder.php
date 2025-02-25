<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Truncate existing records to start fresh
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create permissions
        $permissions = [
            // Customer permissions
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            
            // Voucher permissions
            'view vouchers',
            'create vouchers',
            'edit vouchers',
            'delete vouchers',
            'print vouchers',
            'export vouchers',
            
            // Internet Plan permissions
            'view plans',
            'create plans',
            'edit plans',
            'delete plans',
            
            // Bandwidth Profile permissions
            'view bandwidth profiles',
            'create bandwidth profiles',
            'edit bandwidth profiles',
            'delete bandwidth profiles',
            
            // Router permissions
            'view routers',
            'create routers',
            'edit routers',
            'delete routers',
            'manage router sessions',
            
            // Session permissions
            'view sessions',
            'disconnect sessions',
            
            // Report permissions
            'view reports',
            'export reports',
            
            // Settings permissions
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Super Admin role
        $superAdmin = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        
        // Create Admin role
        $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->givePermissionTo('manage settings');
        
        // Manager role
        $manager = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->givePermissionTo([
            'view customers', 'create customers', 'edit customers',
            'view vouchers', 'create vouchers', 'print vouchers', 'export vouchers',
            'view plans',
            'view bandwidth profiles',
            'view routers', 'manage router sessions',
            'view reports',
            'view sessions',
        ]);
        
        // Staff role
        $staff = Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        $staff->givePermissionTo([
            'view customers',
            'view vouchers', 'create vouchers', 'print vouchers',
            'view plans',
            'view bandwidth profiles',
            'view routers',
            'view sessions',
        ]);
    }
}
