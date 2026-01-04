<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

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

        // Run factories if needed (comment out if not needed)
        $this->runFactories();
    }

    public function runFactories(): void
    {
        // Feed user factory seeder
        $this->call([
            UserFactorySeeder::class,
        ]);

        // Set multiplier if needed
        $multiplier = 5;

        // Create transactions for randomized users
        // based on user count - 3 (admin users) * multiplier
        $n = (int) ((User::count() - 3) / 25) * $multiplier;
        for ($i = 0; $i < $n; $i++) {
            // Create 25 transactions per 25 users randomized
            $this->call(TransactionFactorySeeder::class);
        }

        Log::info($n * 25 . ' transactions created via factory seeder.');
    }
}
