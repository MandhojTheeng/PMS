<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        Permission::create(['name' => 'delete-users']);
        Permission::create(['name' => 'view-users']);

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(['delete-users', 'view-users']);

        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo('view-users');

        Role::create(['name' => 'User']);
    }
}