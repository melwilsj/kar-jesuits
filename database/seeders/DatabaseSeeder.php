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
            FormationStagesSeeder::class,
            RoleTypesSeeder::class,
            DocumentCategoriesSeeder::class,
            RolesAndPermissionsSeeder::class,
            UsersSeeder::class,
            CommunitiesSeeder::class,
            UpdateRelationshipsSeeder::class,
        ]);
    }
}
