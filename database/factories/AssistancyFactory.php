<?php

namespace Database\Factories;

use App\Models\Assistancy;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssistancyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'code' => $this->faker->unique()->bothify('AST-###'),
        ];
    }
} 