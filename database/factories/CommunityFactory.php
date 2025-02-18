<?php

namespace Database\Factories;

use App\Models\{Province, Region};
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'code' => 'COM-' . $this->faker->unique()->numerify('#####'),
            'province_id' => fn() => Province::inRandomOrder()->first()->id,
            'region_id' => function (array $attributes) {
                return Region::where('province_id', $attributes['province_id'])
                    ->inRandomOrder()
                    ->first()?->id;
            },
            'parent_community_id' => null,
            'superior_type' => $this->faker->randomElement(['rector', 'superior', 'coordinator']),
            'address' => $this->faker->address(),
            'diocese' => $this->faker->city(),
            'taluk' => $this->faker->city(),
            'district' => $this->faker->city(),
            'state' => $this->faker->state(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'is_formation_house' => $this->faker->boolean(20),
            'is_attached_house' => $this->faker->boolean(10),
            'is_active' => true
        ];
    }

    public function formationHouse()
    {
        return $this->state(fn (array $attributes) => [
            'is_formation_house' => true
        ]);
    }
} 