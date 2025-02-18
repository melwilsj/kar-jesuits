<?php

namespace Database\Seeders;

use App\Models\{Region, Province};
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'Kohima' , 'code' => 'KHM',  'province_id' => 1],
            ];

        foreach ($regions as $region) {
            Region::firstOrCreate(
                ['code' => $region['code']],
                array_merge($region, ['is_active' => true])
            );
        }
    }
} 