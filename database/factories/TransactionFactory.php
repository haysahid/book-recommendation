<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transactionType = TransactionType::where('slug', 'sale')->first();
        $paymentMethod = PaymentMethod::inRandomOrder()->first();
        $shippingMethod = ShippingMethod::inRandomOrder()->first();

        $data = [
            'user_id' => User::factory(),
            'type_id' => $transactionType->id,
            'code' => strtoupper(fake()->unique()->bothify($transactionType->code_prefix . '-' . '####??')),
            'note' => fake()->sentence(),
            'payment_method_id' => $paymentMethod->id,
            'shipping_method_id' => $shippingMethod->id,
            'voucher_id' => null,
            'voucher_amount' => 0,
            'paid_at' => null,
            'shipped_at' => null,
            'picked_up_at' => null,
            'delivered_at' => null,
            'status' => 'pending',

            'shipping_cost' => 0,
        ];

        if ($shippingMethod->slug === 'courier') {
            $data['rajaongkir_destination_id'] = fake()->numberBetween(1, 1000);
            $data['province_name'] = fake()->state();
            $data['city_name'] = fake()->city();
            $data['district_name'] = fake()->word();
            $data['subdistrict_name'] = fake()->word();
            $data['zip_code'] = fake()->postcode();
            $data['address'] = fake()->address();
            $data['shipping_estimate'] = fake()->numberBetween(1, 7) . ' days';
            $data['shipping_cost'] = fake()->numberBetween(5000, 50000);
        }

        return $data;
    }
}
