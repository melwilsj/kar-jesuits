<?php

namespace Database\Seeders;

use App\Models\{Community, Province};
use Database\Factories\InstitutionFactory;
use Illuminate\Database\Seeder;

class CommunitiesSeeder extends Seeder
{
    public function run(): void
    {
        // Create regular communities for each province
        Province::all()->each(function ($province) {
            // Regular communities
            Community::factory()
                ->count(10)
                ->state(['province_id' => $province->id])
                ->create();

            // Formation houses
            Community::factory()
                ->formationHouse()
                ->count(2)
                ->state(['province_id' => $province->id])
                ->create();

            // Attached houses
            Community::factory()
                ->attachedHouse()
                ->count(3)
                ->state(['province_id' => $province->id])
                ->create();
        });

        // Create just 2 common houses for the entire assistancy
        Community::factory()
            ->commonHouse()
            ->count(2)
            ->create();
    }
} 