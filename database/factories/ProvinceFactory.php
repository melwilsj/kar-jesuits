<?php

namespace Database\Factories;

use App\Models\Assistancy;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProvinceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->state(),
            'code' => $this->faker->unique()->stateAbbr(),
            'country' => 'India',
            'description' => $this->faker->sentence(),
            'assistancy_id' => Assistancy::first()->id ?? Assistancy::factory(),
            'is_active' => true,
        ];
    }
} 