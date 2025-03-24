<?php

namespace Database\Seeders;

use App\Models\Assistancy;
use Illuminate\Database\Seeder;

class AssistancySeeder extends Seeder
{
    public function run(): void
    {
        $assistancies = [
            [
                'name' => 'South Asia',
                'code' => 'SAA',
                'description' => 'South Asian Assistancy'
            ],
            [
                'name' => 'Africa',
                'code' => 'AFR',
                'description' => 'African Assistancy'
            ],
            [
                'name' => 'East Asia',
                'code' => 'EAA',
                'description' => 'East Asian Assistancy'
            ],
            [
                'name' => 'Latin America',
                'code' => 'LAM',
                'description' => 'Latin American Assistancy'
            ],
            [
                'name' => 'Europe',
                'code' => 'EUR',
                'description' => 'European Assistancy'
            ],
            [
                'name' => 'North America',
                'code' => 'NAM',
                'description' => 'North American Assistancy'
            ], 
            [
                'name' => 'Oceania',
                'code' => 'OCE',
                'description' => 'Oceanian Assistancy'
            ],
        ];

        foreach ($assistancies as $assistancy) {
            Assistancy::firstOrCreate(
                ['code' => $assistancy['code']],
                $assistancy
            );
        }
    }
} 