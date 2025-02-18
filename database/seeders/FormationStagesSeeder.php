<?php

namespace Database\Seeders;

use App\Models\FormationStage;
use Illuminate\Database\Seeder;

class FormationStagesSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Novice 1st Year', 'code' => 'N1', 'order' => 1],
            ['name' => 'Novice 2nd Year', 'code' => 'N2', 'order' => 2],
            ['name' => 'Junior', 'code' => 'JUN', 'order' => 3],
            ['name' => 'College Studies', 'code' => 'COL', 'order' => 4],
            ['name' => 'Philosophy', 'code' => 'PHI', 'order' => 5],
            ['name' => 'Regency', 'code' => 'REG', 'order' => 6],
            ['name' => 'PG Studies', 'code' => 'PG', 'order' => 7],
            ['name' => 'Theology', 'code' => 'THE', 'order' => 8],
            ['name' => 'Deacon', 'code' => 'DEA', 'order' => 9],
            ['name' => 'Ordained Priest', 'code' => 'PRI', 'order' => 10],
            ['name' => 'Awaiting Tertianship', 'code' => 'AWT', 'order' => 11],
            ['name' => 'In Tertianship', 'code' => 'TER', 'order' => 12],
            ['name' => 'Awaiting Final Vows', 'code' => 'AFV', 'order' => 13],
        ];

        foreach ($stages as $stage) {
            FormationStage::firstOrCreate(
                ['code' => $stage['code']],
                $stage
            );
        }
    }
} 