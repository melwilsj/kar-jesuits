<?php

namespace Database\Factories;

use App\Models\{Province, Region, User};
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'code' => $this->faker->unique()->bothify('COM-###'),
            'province_id' => Province::first()->id ?? Province::factory(),
            'region_id' => Region::first()->id ?? Region::factory(),
            'superior_id' => null,
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
        ];
    }
} 