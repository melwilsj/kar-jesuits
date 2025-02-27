<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AssistancySeeder::class,
            ProvinceSeeder::class,
            RegionSeeder::class,
            RoleTypesSeeder::class,
            FormationStagesSeeder::class,
            DocumentCategorySeeder::class,
            RolesAndPermissionsSeeder::class,
            CommunitiesSeeder::class,
            UsersSeeder::class,
            UpdateRelationshipsSeeder::class,
            FormationSeeder::class,
        ]);
    }
}
