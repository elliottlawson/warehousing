<?php

namespace Database\Factories;

use App\Enums\RuleType;
use App\Models\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;

class RuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Rule::class;
∫
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->realText('20'),
            'type' => RuleType::getRandomKey(),
            'value' => $this->faker->numerify('##'),∫
        ];
    }
}
