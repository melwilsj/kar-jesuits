<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement([
            'school', 'college', 'university', 'hostel', 
            'community_college', 'iti', 'parish', 
            'social_centre', 'farm', 'retreat_center', 'other'
        ]);
        $name = match($type) {
            'school' => 'St. ' . $this->faker->firstName('male') . ' School',
            'college' => $this->faker->randomElement(['St. ', 'Sacred Heart ', 'Loyola ', '']) . $this->faker->lastName() . ' College',
            'university' => $this->faker->randomElement(['St. ', 'Sacred Heart ', 'Loyola ', '']) . $this->faker->lastName() . ' University',
            'hostel' => 'St. ' . $this->faker->firstName('male') . ' Hostel',
            'community_college' => $this->faker->randomElement(['St. ', 'Sacred Heart ', 'Loyola ', '']) . $this->faker->lastName() . ' Community College',
            'iti' => $this->faker->randomElement(['St. ', 'Sacred Heart ', 'Loyola ', '']) . $this->faker->lastName() . ' ITI',
            'parish' => 'St. ' . $this->faker->firstName('male') . ' Church',
            'retreat_center' => $this->faker->randomElement(['Sacred Heart', 'Divine', 'Holy Spirit', 'Ignatian']) . ' Retreat Center',
            'social_centre' => $this->faker->lastName() . ' Social Service Center',
            default => $this->faker->company()
        };

        return [
            'name' => $name,
            'code' => strtoupper($this->faker->unique()->regexify('[A-Z]{3}[0-9]{2}')),
            'type' => $type,
            'description' => $this->faker->paragraph(),
            'contact_details' => [
                'phones' => [$this->faker->phoneNumber()],
                'emails' => [$this->faker->email()],
                'website' => $this->faker->url()
            ],
            'student_demographics' => $this->getStudentDemographics($type),
            'staff_demographics' => [
                'jesuits' => $this->faker->numberBetween(1, 5),
                'other_religious' => $this->faker->numberBetween(2, 10),
                'catholics' => $this->faker->numberBetween(5, 20),
                'others' => $this->faker->numberBetween(10, 50),
                'total' => $this->faker->numberBetween(50, 100)
            ],
            'beneficiaries' => $this->getBeneficiaries($type),
            'diocese' => $this->faker->city(),
            'taluk' => $this->faker->city(),
            'district' => $this->faker->city(),
            'state' => $this->faker->state(),
            'address' => $this->faker->address(),
            'is_active' => true
        ];
    }

    private function getStudentDemographics($type): ?array
    {
        if (!in_array($type, ['school', 'college', 'university', 'hostel', 'community_college', 'iti'])) {
            return null;
        }

        $total = $this->faker->numberBetween(500, 2000);
        return [
            'catholics' => $this->faker->numberBetween(50, 200),
            'other_christians' => $this->faker->numberBetween(50, 200),
            'non_christians' => $this->faker->numberBetween(400, 1600),
            'boys' => $this->faker->numberBetween(250, 1000),
            'girls' => $this->faker->numberBetween(250, 1000),
            'total' => $total
        ];
    }

    private function getBeneficiaries($type): ?array
    {
        if (!in_array($type, ['social_centre', 'parish'])) {
            return null;
        }

        return [
            'families' => $this->faker->numberBetween(100, 500),
            'individuals' => $this->faker->numberBetween(500, 2000),
            'villages' => $this->faker->numberBetween(5, 20)
        ];
    }
} 