<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Jesuit;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $types = ['regular', 'special'];
        $eventTypes = ['birthday', 'jubilee', 'seminar', 'talk', 'retreat', 'meeting', 'other'];
        $startDate = $this->faker->dateTimeBetween('now', '+6 months');
        
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement($types),
            'event_type' => $this->faker->randomElement($eventTypes),
            'start_datetime' => $startDate,
            'end_datetime' => (clone $startDate)->modify('+' . rand(1, 5) . ' hours'),
            'venue' => $this->faker->optional(0.7)->address(),
            'province_id' => Province::inRandomOrder()->first()->id ?? null,
            'region_id' => $this->faker->optional(0.5)->randomElement(Region::pluck('id')->toArray()),
            'jesuit_id' => $this->faker->optional(0.3)->randomElement(Jesuit::pluck('id')->toArray()),
            'community_id' => $this->faker->optional(0.3)->randomElement(Community::pluck('id')->toArray()),
            'is_public' => $this->faker->boolean(80),
            'is_recurring' => $this->faker->boolean(20),
            'recurrence_pattern' => $this->faker->optional(0.2)->randomElement(['yearly', 'monthly', 'weekly']),
            'created_by' => User::inRandomOrder()->first()->id ?? 1,
        ];
    }
} 