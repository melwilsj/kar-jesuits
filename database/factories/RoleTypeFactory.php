<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleTypeFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['province', 'community', 'institution'];
        
        return [
            'name' => $this->faker->jobTitle(),
            'category' => $this->faker->randomElement($categories),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
} 