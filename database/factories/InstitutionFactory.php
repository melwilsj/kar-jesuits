<?php

namespace Database\Factories;

use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'community_id' => Community::inRandomOrder()->first()->id ?? Community::factory(),
            'type' => $this->faker->randomElement(['school', 'college', 'university', 'hostel', 
                'community_college', 'iti', 'parish', 'social_centre', 'farm', 'ngo', 'other']),
            'description' => $this->faker->paragraph(),
            'contact_details' => json_encode([
                'address' => $this->faker->address(),
                'phone' => $this->faker->phoneNumber(),
                'email' => $this->faker->companyEmail(),
                'website' => $this->faker->url(),
            ]),
            'student_demographics' => null,
            'staff_demographics' => json_encode([
                'teaching' => $this->faker->numberBetween(10, 50),
                'non_teaching' => $this->faker->numberBetween(5, 20),
                'support' => $this->faker->numberBetween(3, 15),
            ]),
            'diocese' => $this->faker->city(),
            'taluk' => $this->faker->city(),
            'district' => $this->faker->city(),
            'state' => $this->faker->state(),
            'is_active' => true,
        ];
    }
} 