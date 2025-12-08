<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Dashboard and Reports
            'dashboard.view',
            'reports.view',

            // User Management
            'user.view',
            'user.manage',

            // Book Management
            'book.view',
            'book.create',
            'book.update',
            'book.delete',

            // Order Management
            'order.view',
            'order.manage',

            // Category Management
            'category.view',
            'category.manage',

            // Review Management
            'review.view',
            'review.manage',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles
        $roles = [
            'Super Admin',
            'Admin',
            'User',
            'Guest',
        ];

        // Create roles
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Assign permissions to roles
        $superAdmin = Role::findByName('Super Admin');
        $admin = Role::findByName('Admin');
        $user = Role::findByName('User');

        // Super Admin: Full access
        $superAdmin->givePermissionTo(Permission::all());

        // Admin: Manage books, orders, categories, and reviews
        $admin->givePermissionTo([
            'dashboard.view',
            'reports.view',
            'book.view',
            'book.create',
            'book.update',
            'book.delete',
            'order.view',
            'order.manage',
            'category.view',
            'category.manage',
            'review.view',
            'review.manage',
        ]);

        // User: Limited access to view books and orders
        $user->givePermissionTo([
            'dashboard.view',
            'book.view',
            'order.view',
            'review.view',
        ]);
    }
}
