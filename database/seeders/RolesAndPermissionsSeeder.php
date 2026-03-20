<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::findOrCreate('assign tasks');
        Permission::findOrCreate('view assigned tasks');
        Permission::findOrCreate('update task status');
        Permission::findOrCreate('manage contents');

        // Create roles and assign created permissions
        $role = Role::findOrCreate('restricted-admin');
        $role->givePermissionTo(['view assigned tasks', 'update task status', 'manage contents']);

        $role = Role::findOrCreate('super-admin');
        $role->givePermissionTo(Permission::all());
    }
}
