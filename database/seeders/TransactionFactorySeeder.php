<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionFactorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set user IDs range & shuffle to get random users
        $userIds = range(4, 103);
        shuffle($userIds);

        // Limit users
        $userIds = array_slice($userIds, 0, 25);

        foreach ($userIds as $userId) {
            Transaction::factory()->create([
                'user_id' => $userId,
            ]);
        }
    }
}
