<?php

namespace Database\Seeders;

use App\Models\{Region, Province};
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            // Karnataka Province Regions
            ['name' => 'Kohima', 'code' => 'KHM', 'province_id' => 1],
            // Madurai Province Regions
            ['name' => 'Nepal', 'code' => 'NEP', 'province_id' => 2]
        ];

        foreach ($regions as $region) {
            Region::firstOrCreate(
                ['code' => $region['code']],
                $region
            );
        }
    }
} 