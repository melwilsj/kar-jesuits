<?php

namespace Database\Seeders;

use App\Models\Assistancy;
use Illuminate\Database\Seeder;

class AssistancySeeder extends Seeder
{
    public function run(): void
    {
        $assistancies = [
            ['name' => 'South Asia', 'code' => 'SAS'],
            ['name' => 'East Asia and Oceania', 'code' => 'EAO'],
            ['name' => 'Africa and Madagascar', 'code' => 'AFR'],
            ['name' => 'Latin America - North', 'code' => 'LAN'],
            ['name' => 'Latin America - South', 'code' => 'LAS'],
            ['name' => 'Europe - South', 'code' => 'EUS'],
            ['name' => 'Europe - Central and East', 'code' => 'ECE'],
            ['name' => 'North America', 'code' => 'NAM'],
        ];

        foreach ($assistancies as $assistancy) {
            Assistancy::create($assistancy);
        }
    }
} 