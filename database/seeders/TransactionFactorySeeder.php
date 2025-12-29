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
        // Select 10 randomized user between ID 4 to 43
        $userIds = range(4, 43);
        shuffle($userIds);
        $userIds = array_slice($userIds, 0, 10);

        foreach ($userIds as $userId) {
            $transaction = Transaction::factory()->create([
                'user_id' => $userId,
            ]);

            $items = TransactionItem::factory()->count(rand(1, 5))->create([
                'transaction_id' => $transaction->id,
            ]);

            $finalAmount = $items->sum('subtotal') + $transaction->shipping_cost;

            Invoice::factory()->create([
                'transaction_id' => $transaction->id,
                'base_amount' => $items->sum('subtotal'),
                'shipping_cost' => $transaction->shipping_cost,
                'amount' => $finalAmount,
            ]);

            Payment::factory()->create([
                'transaction_id' => $transaction->id,
                'payment_method_id' => $transaction->payment_method_id,
                'amount' => $finalAmount,
            ]);
        }
    }
}
