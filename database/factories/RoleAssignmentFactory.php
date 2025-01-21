<?php

namespace Database\Factories;

use App\Models\{User, RoleType, Community, Institution};
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleAssignmentFactory extends Factory
{
    public function definition(): array
    {
        $roleType = RoleType::inRandomOrder()->first();
        
        // Get assignable type based on role category
        $assignableType = match ($roleType->category) {
            'community' => Community::class,
            'institution' => Institution::class,
            default => Community::class,
        };

        // Get a random assignable entity
        $assignable = $assignableType::inRandomOrder()->first();

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'role_type_id' => $roleType->id,
            'assignable_type' => $assignableType,
            'assignable_id' => $assignable->id,
            'start_date' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
            'end_date' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'is_active' => false, // Set to false by default to avoid unique constraint
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
} 