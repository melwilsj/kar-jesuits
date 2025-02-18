<?php

namespace Database\Factories;

use App\Models\{Province, Community};
use Illuminate\Database\Eloquent\Factories\Factory;

class JesuitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'province_id' => fn() => Province::inRandomOrder()->first()->id,
            'current_community_id' => fn() => Community::inRandomOrder()->first()?->id,
            'code' => 'J' . $this->faker->unique()->numberBetween(1000, 9999),
            'prefix_modifier' => null,
            'category' => $this->faker->randomElement(['Bp', 'P', 'S', 'NS', 'F']),
            'dob' => $this->faker->dateTimeBetween('-70 years', '-25 years'),
            'dod' => null,
            'joining_date' => $this->faker->dateTimeBetween('-30 years', '-1 year'),
            'priesthood_date' => function (array $attributes) {
                return in_array($attributes['category'], ['Bp', 'P']) 
                    ? $this->faker->dateTimeBetween($attributes['joining_date'], 'now')
                    : null;
            },
            'final_vows_date' => function (array $attributes) {
                return in_array($attributes['category'], ['Bp', 'P', 'F']) 
                    ? $this->faker->dateTimeBetween($attributes['joining_date'], 'now')
                    : null;
            },
            'status' => 'active',
            'photo_url' => null,
            'is_active' => true,
            'academic_qualifications' => $this->faker->randomElements(['BA', 'MA', 'PhD', 'BTh', 'MTh'], 2),
            'languages' => $this->faker->randomElements(['English', 'Hindi', 'Tamil', 'Malayalam'], 3),
            'publications' => $this->faker->optional()->sentences(3),
            'notes' => $this->faker->optional()->paragraph(),
            'is_external' => false
        ];
    }
} 