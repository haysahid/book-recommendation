<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethod::insert([
            [
                'name' => 'COD',
                'slug' => 'cod',
                'description' => 'Cash On Delivery.',
            ],
            [
                'name' => 'Transfer',
                'slug' => 'transfer',
                'description' => 'Bank transfer or e-wallet payment method.',
            ],
        ]);
    }
}
