<?php

namespace Database\Seeders;

use App\Models\RoleType;
use Illuminate\Database\Seeder;

class RoleTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Assistancy level roles
            ['name' => 'POSA', 'category' => 'assistancy', 'description' => 'President of South Asia'],
            
            // Community level roles
            ['name' => 'Superior', 'category' => 'community', 'description' => 'Head of a Community'],
            ['name' => 'Vice-Superior', 'category' => 'community', 'description' => 'Head of a Community'],
            ['name' => 'Rector', 'category' => 'community', 'description' => 'Head of a Formation House'],
            ['name' => 'Coordinator', 'category' => 'community', 'description' => 'Head of an Attached House'],
            ['name' => 'Minister', 'category' => 'community', 'description' => 'Community Minister'],
            ['name' => 'Treasurer', 'category' => 'community', 'description' => 'Community Treasurer'],
            
            // Province level roles
            ['name' => 'Provincial', 'category' => 'province', 'description' => 'Head of a Province'],
            ['name' => 'Socius', 'category' => 'province', 'description' => 'Provincial Socius'],
            ['name' => 'Treasurer', 'category' => 'province', 'description' => 'Provincial Treasurer'],
            
            // Institution level roles
            ['name' => 'Principal', 'category' => 'institution', 'description' => 'Head of an Institution'],
            ['name' => 'Director', 'category' => 'institution', 'description' => 'Head of an Institution'],
            ['name' => 'Administrator', 'category' => 'institution', 'description' => 'Head of an Institution'],
            ['name' => 'Parish Priest', 'category' => 'institution', 'description' => 'Head of an Institution'],
            ['name' => 'Vice-Chancellor', 'category' => 'institution', 'description' => 'Head of an Institution'],

            // Commission level roles
            ['name' => 'Commission Head', 'category' => 'commission', 'description' => 'Head of a Commission'],
            ['name' => 'Commission Member', 'category' => 'commission', 'description' => 'Member of a Commission'],
        ];

        foreach ($types as $type) {
            RoleType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'category' => $type['category'],
                    'description' => $type['description']
                ]
            );
        }
    }
} 