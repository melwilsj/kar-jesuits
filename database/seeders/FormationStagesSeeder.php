<?php

namespace Database\Seeders;

use App\Models\FormationStage;
use Illuminate\Database\Seeder;

class FormationStagesSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Novice 1st Year', 'order' => 1, 'has_years' => false],
            ['name' => 'Novice 2nd Year', 'order' => 2, 'has_years' => false],
            ['name' => 'Junior', 'order' => 3, 'has_years' => false],
            ['name' => 'College Studies', 'order' => 4, 'has_years' => true, 'max_years' => 4],
            ['name' => 'Philosophy', 'order' => 5, 'has_years' => true, 'max_years' => 2],
            ['name' => 'Regency', 'order' => 6, 'has_years' => true, 'max_years' => 5],
            ['name' => 'PG Studies', 'order' => 7, 'has_years' => true, 'max_years' => 3],
            ['name' => 'Theology', 'order' => 8, 'has_years' => true, 'max_years' => 3],
            ['name' => 'Deacon', 'order' => 9, 'has_years' => false],
            ['name' => 'Ordained Priest', 'order' => 10, 'has_years' => false],
            ['name' => 'Awaiting Tertianship', 'order' => 11, 'has_years' => false],
            ['name' => 'In Tertianship', 'order' => 12, 'has_years' => false],
            ['name' => 'Awaiting Final Vows', 'order' => 13, 'has_years' => false],
        ];

        foreach ($stages as $stage) {
            FormationStage::create($stage);
        }
    }
} 