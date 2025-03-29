<?php

namespace Database\Factories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->city(),
            'code' => $this->faker->unique()->bothify('REG-###'),
            'country' => 'India',
            'description' => $this->faker->sentence(),
            'province_id' => Province::first()->id ?? Province::factory(),
            'assistancy_id' => Assistancy::first()->id ?? Assistancy::factory(),
        ];
    }
}