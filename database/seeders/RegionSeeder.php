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
            Region::create([
                'name' => $region['name'],
                'code' => $region['code'],
                'province_id' => $region['province_id']
            ]);
        }
    }
} 