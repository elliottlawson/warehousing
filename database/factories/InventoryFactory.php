<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Inventory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'item_number' => $this->faker->randomNumber(9),
            'description' => $this->faker->sentence(),
        ];
    }

    public function deleted(): self
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }
}
