<?php

namespace Database\Factories;

use App\Models\Types\LocationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = LocationType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name'         => $this->faker->name,
            'description'  => $this->faker->sentence(),
            'abbreviation' => $this->faker->text(5),
        ];
    }
}
