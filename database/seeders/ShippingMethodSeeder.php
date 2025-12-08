<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShippingMethod::insert([
            [
                'name' => 'Pickup at Store',
                'slug' => 'pickup',
                'description' => 'Pick up items directly at the store.',
            ],
            [
                'name' => 'Courier',
                'slug' => 'courier',
                'description' => 'Delivery using courier services. Costs depend on location and item weight. Estimated delivery time is 1-5 business days.',
            ],
        ]);
    }
}
