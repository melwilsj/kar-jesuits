<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            ['name' => 'Karnataka', 'code' => 'KAR', 'assistancy_id' => 1],
            ['name' => 'Kerala', 'code' => 'KER', 'assistancy_id' => 1],
            ['name' => 'Madurai', 'code' => 'MDU', 'assistancy_id' => 1],
            ['name' => 'Delhi', 'code' => 'DEL', 'assistancy_id' => 1],
            ['name' => 'Bombay', 'code' => 'BOM', 'assistancy_id' => 1],
            ['name' => 'Calcutta', 'code' => 'CAL', 'assistancy_id' => 1],
        ];

        foreach ($provinces as $province) {
            Province::create($province);
        }
    }
} 