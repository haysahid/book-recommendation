<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $book = Book::inRandomOrder()->first();
        // 80% chance for quantity = 1, 20% chance for quantity = 2
        $quantity = $this->faker->boolean(80) ? 1 : 2;

        return [
            'store_id' => 1,
            'transaction_id' => null,
            'user_id' => null,
            'book_id' => $book->id,
            'quantity' => $quantity,
            'unit_base_price' => $book->slice_price,
            'unit_discount_type' => 'percentage',
            'unit_discount' => $book->discount,
            'unit_final_price' => $book->final_price,
            'subtotal' => $book->final_price * $quantity,
            'fullfillment_status' => 'pending',
            'rating' => null,
            'review' => null,
            'reviewed_at' => null,
            'created_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($transactionItem) {
            // If fullfillment status is completed, add rating and review randomly
            if ($transactionItem->fullfillment_status === 'completed') {
                $transactionItem->rating = $this->faker->optional()->numberBetween(1, 5);
                $transactionItem->review = $transactionItem->rating ? $this->faker->optional()->sentence() : null;

                // Reviewed at is a date between transaction created_at and +7 days
                $transactionItem->reviewed_at = $transactionItem->rating
                    ? $this->faker->dateTimeBetween($transactionItem->created_at, $transactionItem->created_at->addDays(7))
                    : null;

                $transactionItem->save();
            }
        });
    }
}
