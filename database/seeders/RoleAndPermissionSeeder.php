<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            'manage permissions',
            'view enquiries',
            'create enquiries',
            'edit enquiries',
            'delete enquiries',
            'view quotations',
            'create quotations',
            'edit quotations',
            'delete quotations',
            'view purchase orders',
            'create purchase orders',
            'edit purchase orders',
            'delete purchase orders',
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            'view products',
            'create products',
            'edit products',
            'delete products',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign existing permissions
        $adminRole = Role::findOrCreate('Admin');
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::findOrCreate('Manager');
        $managerRole->givePermissionTo([
            'view users',
            'view enquiries',
            'create enquiries',
            'edit enquiries',
            'view quotations',
            'create quotations',
            'edit quotations',
        ]);

        $siteEngineerRole = Role::findOrCreate('Site Engineer');
        $siteEngineerRole->givePermissionTo([
            'view enquiries',
            'view quotations',
        ]);

        $userRole = Role::findOrCreate('User');
        $userRole->givePermissionTo([
            'view enquiries',
        ]);
    }
}
