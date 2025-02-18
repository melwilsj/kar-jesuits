<?php

namespace Database\Seeders;

use App\Models\{Community, Province};
use Database\Factories\InstitutionFactory;
use Illuminate\Database\Seeder;

class CommunitiesSeeder extends Seeder
{
    public function run(): void
    {
        Province::all()->each(function ($province) {
            Community::factory(rand(2, 3))
                ->create(['province_id' => $province->id])
                ->each(function ($community) {
                    // Create institutions
                    $community->institutions()->saveMany(
                        InstitutionFactory::times(rand(1, 3))->make()
                    );
                });
        });
    }
} 