<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersData = [
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@example.com',
                'password' => 'superadmin2025',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'admin2025',
                'role' => 'Admin',
            ],
            [
                'name' => 'User',
                'username' => 'user',
                'email' => 'user@example.com',
                'password' => 'user2025',
                'role' => 'User',
            ],
        ];

        foreach ($usersData as $userData) {
            // Check if the user already exists
            $user = User::where('email', $userData['email'])
                ->orWhere('username', $userData['username'])
                ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'username' => $userData['username'],
                    'password' => Hash::make($userData['password']),
                ]);
            }

            // Find the role and assign it
            $role = Role::findByName($userData['role']);
            if ($role) {
                // Ensure the user has only one primary role
                $user->syncRoles([$role]);
            }

            // Delay 0.5 seconds between creations
            usleep(500000);
        }
    }
}
