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
                'description' => 'Karnataka Jesuit Province',
                'is_active' => true
            ],
            [
                'name' => 'Darjeeling',
                'code' => 'DGR',
                'assistancy_id' => 1,
                'description' => 'Darjeeling Jesuit Province',
                'is_active' => true
            ],
            [
                'name' => 'Madurai',
                'code' => 'MDU',
                'assistancy_id' => 1,
                'description' => 'Madurai Jesuit Province',
                'is_active' => true
            ],
            [
                'name' => 'Delhi',
                'code' => 'DEL',
                'assistancy_id' => 1,
                'description' => 'Delhi Jesuit Province',
                'is_active' => true
            ],
            [
                'name' => 'Bombay',
                'code' => 'BOM',
                'assistancy_id' => 1,
                'description' => 'Bombay Jesuit Province',
                'is_active' => true
            ],
            [
                'name' => 'Calcutta',
                'code' => 'CAL',
                'assistancy_id' => 1,
                'description' => 'Calcutta Jesuit Province',
                'is_active' => true
            ],
            [
                'name' => 'Kerala',
                'code' => 'KER',
                'assistancy_id' => 1,
                'description' => 'Kerala Jesuit Province',
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