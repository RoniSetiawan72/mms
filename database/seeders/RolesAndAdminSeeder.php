<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Permissions
        $permissions = [
            // Master Data Permissions
            'view master data',
            'create master data',
            'edit master data',
            'delete master data',

            // Production Permissions
            'view work orders',
            'create work orders',
            'start production',
            'finish production',

            // Inventory Permissions
            'receive materials',
            'view stock movements',
            'adjust stock'
        ];

        // Create permissions in DB
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Administrator Role and assign all permissions
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $adminRole->givePermissionTo(Permission::all());

        // 3. Create Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mms.local'],
            [
                'name' => 'Roni Setiawan',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 4. Assign Role to User
        $adminUser->syncRoles(['Administrator']);

        $this->command->info('Roles, Permissions, and Admin user seeded successfully.');
    }
}
