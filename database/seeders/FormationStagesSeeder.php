<?php

namespace Database\Seeders;

use App\Models\FormationStage;
use Illuminate\Database\Seeder;

class FormationStagesSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Novice (1st year)', 'code' => 'NOV1', 'order' => 1],
            ['name' => 'Novice (2nd year)', 'code' => 'NOV2', 'order' => 2],
            ['name' => 'Junior', 'code' => 'JUN', 'order' => 3],
            ['name' => 'College Studies (1st year)', 'code' => 'COL1', 'order' => 4],
            ['name' => 'College Studies (2nd year)', 'code' => 'COL2', 'order' => 5],
            ['name' => 'College Studies (3rd year)', 'code' => 'COL3', 'order' => 6],
            ['name' => 'College Studies (4th year)', 'code' => 'COL4', 'order' => 7],
            ['name' => 'Philosophy (1st year)', 'code' => 'PHI1', 'order' => 8],
            ['name' => 'Philosophy (2nd year)', 'code' => 'PHI2', 'order' => 9],
            ['name' => 'Regency (1st year)', 'code' => 'REG1', 'order' => 10],
            ['name' => 'Regency (2nd year)', 'code' => 'REG2', 'order' => 11],
            ['name' => 'PG Studies (1st year)', 'code' => 'PG1', 'order' => 15],
            ['name' => 'PG Studies (2nd year)', 'code' => 'PG2', 'order' => 16],
            ['name' => 'Theology (1st year)', 'code' => 'THE1', 'order' => 17],
            ['name' => 'Theology (2nd year)', 'code' => 'THE2', 'order' => 18],
            ['name' => 'Theology (3rd year)', 'code' => 'THE3', 'order' => 19],
            ['name' => 'Deacon', 'code' => 'DEA', 'order' => 20],
            ['name' => 'Ordained Priest', 'code' => 'ORD', 'order' => 21],
            ['name' => 'Awaiting Tertianship', 'code' => 'AWT', 'order' => 22],
            ['name' => 'In Tertianship', 'code' => 'TER', 'order' => 23],
            ['name' => 'Awaiting Final Vows', 'code' => 'AFV', 'order' => 24]
        ];

        foreach ($stages as $stage) {
            FormationStage::create($stage);
        }
    }
} 