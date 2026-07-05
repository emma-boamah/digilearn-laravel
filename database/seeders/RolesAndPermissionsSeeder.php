<?php

namespace Database\Seeders;

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
        
        // School specific permissions
        Permission::findOrCreate('manage school settings');
        Permission::findOrCreate('manage academic terms');
        Permission::findOrCreate('manage school classes');
        Permission::findOrCreate('manage users');
        Permission::findOrCreate('manage assessments');
        Permission::findOrCreate('view own grades');

        // Create roles and assign created permissions
        $role = Role::findOrCreate('school-admin');
        $role->givePermissionTo(['manage school settings', 'manage academic terms', 'manage school classes', 'manage users', 'manage assessments']);

        $role = Role::findOrCreate('teacher');
        $role->givePermissionTo(['manage assessments', 'manage school classes']);

        $role = Role::findOrCreate('student');
        $role->givePermissionTo(['view own grades']);

        $role = Role::findOrCreate('parent');
        $role->givePermissionTo(['view own grades']);
        $role = Role::findOrCreate('restricted-admin');
        $role->givePermissionTo(['view assigned tasks', 'update task status', 'manage contents']);

        $role = Role::findOrCreate('super-admin');
        $role->givePermissionTo(Permission::all());
    }
}
