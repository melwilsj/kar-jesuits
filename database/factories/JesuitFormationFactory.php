<?php

namespace Database\Factories;

use App\Models\{User, FormationStage};
use Illuminate\Database\Eloquent\Factories\Factory;

class JesuitFormationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'stage_id' => FormationStage::first()->id ?? FormationStage::factory(),
            'current_year' => $this->faker->numberBetween(1, 4),
            'start_date' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
            'end_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }
} 