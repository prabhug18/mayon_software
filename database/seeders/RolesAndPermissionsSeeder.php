<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            'manage users',
            'manage roles',
            'view reports',
            'edit profile',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

    // Create roles
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $userRole = Role::firstOrCreate(['name' => 'User']);
    $siteEngineerRole = Role::firstOrCreate(['name' => 'Site Engineer']);

    // Assign all permissions to Admin
    $adminRole->syncPermissions($permissions);
    // Assign limited permissions to User
    $userRole->syncPermissions(['edit profile']);
    // Assign permissions to Site Engineer (view reports + edit profile)
    $siteEngineerRole->syncPermissions(['view reports','edit profile']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
            ]
        );
        // Ensure mobile is set even if user already exists
        $admin->mobile = $admin->mobile ?? '+10000000000';
        $admin->save();
        $admin->assignRole($adminRole);

        // Create multiple Site Engineer users
        for ($i=1;$i<=6;$i++) {
            $email = 'engineer'.$i.'@example.com';
            $user = User::firstOrCreate([
                'email' => $email
            ], [
                'name' => 'Site Engineer '.$i,
                'password' => Hash::make('12345678'),
            ]);
            // Ensure mobile is set/updated
            $user->mobile = $user->mobile ?? ('+1000000000' . $i);
            $user->save();
            $user->assignRole($siteEngineerRole);
        }
    }
}
