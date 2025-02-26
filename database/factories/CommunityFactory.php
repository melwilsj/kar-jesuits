<?php

namespace Database\Factories;

use App\Models\{Province, Region, Assistancy};
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'province_id' => Province::factory(),
            'superior_type' => $this->faker->randomElement(['Superior', 'Rector', 'Coordinator']),
            'address' => $this->faker->address,
            'diocese' => $this->faker->city,
            'taluk' => $this->faker->city,
            'district' => $this->faker->city,
            'state' => $this->faker->state,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'is_formation_house' => false,
            'is_attached_house' => false,
            'is_common_house' => false,
            'is_active' => true
        ];
    }

    public function formationHouse(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_formation_house' => true
        ]);
    }

    public function attachedHouse(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_attached_house' => true,
            'superior_type' => 'Coordinator'
        ]);
    }

    public function commonHouse(): self
    {
        return $this->state(fn (array $attributes) => [
            'province_id' => null,
            'region_id' => null,
            'assistancy_id' => fn() => Assistancy::factory()->create()->id,
            'is_common_house' => true
        ]);
    }
} 