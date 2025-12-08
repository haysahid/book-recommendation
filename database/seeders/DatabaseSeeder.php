<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            StoreSeeder::class,
            BookSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
