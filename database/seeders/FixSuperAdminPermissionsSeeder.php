<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixSuperAdminPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Get all permissions
        $permissions = Permission::all();

        // Get the Super Admin role
        $superAdmin = Role::where('name', 'Super Admin')->first();

        // Give all permissions to Super Admin
        $superAdmin->syncPermissions($permissions);
    }
}
