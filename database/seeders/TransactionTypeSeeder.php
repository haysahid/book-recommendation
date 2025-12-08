<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TransactionType::insert([
            [
                'name' => 'Purchase',
                'slug' => 'purchase',
                'code_prefix' => 'PR',
                'description' => 'Transaction for purchasing goods.',
                'effect_on_stock' => 'inbound',
            ],
            [
                'name' => 'Sale',
                'slug' => 'sale',
                'code_prefix' => 'SL',
                'description' => 'Transaction for selling goods to customers.',
                'effect_on_stock' => 'outbound',
            ],
            [
                'name' => 'Return to Supplier',
                'slug' => 'return-to-supplier',
                'code_prefix' => 'RS',
                'description' => 'Transaction for returning goods to the supplier.',
                'effect_on_stock' => 'outbound',
            ],
            [
                'name' => 'Return from Customer',
                'slug' => 'return-from-customer',
                'code_prefix' => 'RC',
                'description' => 'Transaction for returning goods from customers.',
                'effect_on_stock' => 'inbound',
            ],
            [
                'name' => 'Lost or Damaged Goods',
                'slug' => 'damaged-out',
                'code_prefix' => 'DO',
                'description' => 'Transaction for lost or damaged goods.',
                'effect_on_stock' => 'outbound',
            ],
            [
                'name' => 'Internal Use',
                'slug' => 'internal-use',
                'code_prefix' => 'IU',
                'description' => 'Transaction for internal use of goods.',
                'effect_on_stock' => 'outbound',
            ],
            [
                'name' => 'Stock Adjustment In',
                'slug' => 'adjustment-in',
                'code_prefix' => 'AI',
                'description' => 'Transaction for stock adjustment inbound.',
                'effect_on_stock' => 'inbound',
            ],
            [
                'name' => 'Stock Adjustment Out',
                'slug' => 'adjustment-out',
                'code_prefix' => 'AO',
                'description' => 'Transaction for stock adjustment outbound.',
                'effect_on_stock' => 'outbound',
            ],
        ]);
    }
}
