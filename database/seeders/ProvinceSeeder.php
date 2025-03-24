<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            [
                'name' => 'Karnataka',
                'code' => 'KAR',
                'assistancy_id' => 1,
                'country' => 'India',
                'description' => 'Karnataka Jesuit Province',
                'is_active' => true
            ],
            [
                'name' => 'Darjeeling',
                'code' => 'DAR',
                'assistancy_id' => 1,
                'country' => 'India',
                'description' => 'Darjeeling Jesuit Province',
                'is_active' => true
            ]
        ];

        foreach ($provinces as $province) {
            Province::firstOrCreate(
                ['code' => $province['code']],
                $province
            );
        }
    }
} 