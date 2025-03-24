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
            JesuitsSeeder::class,
            AdminUsersSeeder::class,
            CommissionsSeeder::class,
            UpdateRelationshipsSeeder::class,
            FormationSeeder::class,
            EventsSeeder::class,
            NotificationsSeeder::class
        ]);
    }
}
