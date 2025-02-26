<?php

namespace Database\Factories;

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
            'type' => $this->faker->randomElement(['admin', 'staff', 'jesuit', 'guest']),
            'is_active' => true,
            'phone_number' => '+91' . $this->faker->numerify('##########')
        ];
    }
}