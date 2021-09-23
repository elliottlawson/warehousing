<?php

namespace Database\Factories;

use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Transactions;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Transactions::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'type' => TransactionType::ADD,
            'quantity' => $this->faker->randomNumber(3),
            'direction' => TransactionDirection::FROM,
        ];
    }
}
