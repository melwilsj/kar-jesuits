<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        $types = ['event', 'news', 'announcement', 'birthday', 'feast_day', 'death', 'other'];
        $sentChance = $this->faker->boolean(70);
        
        return [
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement($types),
            'event_id' => $this->faker->optional(0.6)->randomElement(Event::pluck('id')->toArray()),
            'scheduled_for' => $sentChance ? null : $this->faker->dateTimeBetween('now', '+1 month'),
            'sent_at' => $sentChance ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'is_sent' => $sentChance,
            'metadata' => null,
            'created_by' => User::inRandomOrder()->first()->id ?? 1,
        ];
    }
} 