<?php

namespace Database\Factories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'type' => $this->faker->randomElement(['P', 'S', 'NS', 'F']),
            'province_id' => Province::first()->id ?? Province::factory(),
            'current_community_id' => null,
            'is_active' => true,
        ];
    }
}