<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Jesuit;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JesuitFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['P', 'S', 'NS', 'F', 'Bp'];
        $category = $this->faker->randomElement($categories);
        $joiningDate = $this->faker->dateTimeBetween('-40 years', '-1 year');
        
        $priestHoodDate = null;
        $finalVowsDate = null;
        
        // Only priests and bishops have priesthood date
        if (in_array($category, ['P', 'Bp'])) {
            $priestHoodDate = $this->faker->dateTimeBetween($joiningDate, 'now');
            $finalVowsDate = $this->faker->dateTimeBetween($priestHoodDate, 'now');
        }
        
        // For brothers, they may have final vows but not priesthood
        if ($category === 'F') {
            $finalVowsDate = $this->faker->dateTimeBetween($joiningDate, 'now');
        }
        
        $province = Province::inRandomOrder()->first();
        
        return [
            'user_id' => User::factory()->create([
                'name' => $this->faker->name('male'),
                'email' => $this->faker->unique()->safeEmail(),
                'type' => 'jesuit'
            ])->id,
            'province_id' => $province->id,
            'region_id' => $province->regions()->inRandomOrder()->first()?->id,
            'current_community_id' => null, // Will be set later
            'code' => strtoupper($this->faker->unique()->regexify('[A-Z]{3}[0-9]{4}')),
            'category' => $category,
            'dob' => $this->faker->dateTimeBetween('-80 years', '-20 years'),
            'prefix_modifier' => $this->faker->randomElement([null, '+', '-', '*']),
            'photo_url' => null,
            'joining_date' => $joiningDate,
            'priesthood_date' => $priestHoodDate,
            'final_vows_date' => $finalVowsDate,
            'dod' => null,
            'is_active' => true,
            'status' => 'Active',
            'academic_qualifications' => json_encode([
                ['degree' => 'BA', 'field' => 'Philosophy', 'year' => $this->faker->year()],
                ['degree' => 'MA', 'field' => 'Theology', 'year' => $this->faker->year()]
            ]),
            'publications' => json_encode([]),
            'languages' => json_encode(['English', $this->faker->randomElement(['Hindi', 'Tamil', 'Malayalam', 'Kannada', 'Spanish'])]),
            'is_external' => false,
            'notes' => $this->faker->paragraph(),
            'ministry' => $this->faker->randomElement(['Education', 'Parish', 'Social Work', 'Retreat Ministry', 'Formation']),
        ];
    }
    
    public function formation(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => $this->faker->randomElement(['NS', 'S']),
            'final_vows_date' => null,
            'priesthood_date' => null,
        ]);
    }
    
    public function inExternalLocation(): self
    {
        return $this->state(fn (array $attributes) => [
            'current_community_id' => null,
            'status' => 'External Location',
            'notes' => 'Residing at: ' . $this->faker->address . ', Contact: ' . $this->faker->phoneNumber
        ]);
    }
    
    public function inForeignProvince(): self
    {
        return $this->state(function (array $attributes) {
            $originalProvince = Province::find($attributes['province_id']);
            $foreignProvince = Province::where('id', '!=', $originalProvince->id)
                ->inRandomOrder()->first();
                
            return [
                'prefix_modifier' => '+',
                'region_id' => $foreignProvince->regions()->inRandomOrder()->first()?->id,
                'notes' => "Originally from {$originalProvince->name} province, currently in {$foreignProvince->name} province. " . $this->faker->sentence()
            ];
        });
    }
} 