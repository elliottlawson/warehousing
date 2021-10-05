<?php

namespace Database\Factories\Types;

use Illuminate\Database\Eloquent\Factories\Factory;

abstract class TypeFactory extends Factory
{
    /**
     * Note: When extending, define a $model property with the appropriate class name
     */

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
