<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AssistancySeeder::class,
            CommonHouseSeeder::class,
            ProvinceSeeder::class,
            RegionSeeder::class,
            RoleTypesSeeder::class,
            FormationStagesSeeder::class,
            DocumentCategoriesSeeder::class,
            RolesAndPermissionsSeeder::class,
            CommunitiesSeeder::class,
            UsersSeeder::class,
            UpdateRelationshipsSeeder::class,
        ]);
    }
}
