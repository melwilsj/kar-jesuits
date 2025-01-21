<?php

namespace Database\Seeders;

use App\Models\RoleType;
use Illuminate\Database\Seeder;

class RoleTypesSeeder extends Seeder
{
    public function run(): void
    {
        $roleTypes = [
            // Province Roles
            ['name' => 'Provincial', 'category' => 'province'],
            ['name' => 'Socius', 'category' => 'province'],
            ['name' => 'Treasurer', 'category' => 'province'],
            
            // Community Roles
            ['name' => 'Rector', 'category' => 'community'],
            ['name' => 'Superior', 'category' => 'community'],
            ['name' => 'Minister', 'category' => 'community'],
            ['name' => 'Treasurer', 'category' => 'community'],
            
            // Institution Roles
            ['name' => 'Principal', 'category' => 'institution'],
            ['name' => 'Director', 'category' => 'institution'],
            ['name' => 'Administrator', 'category' => 'institution'],
            ['name' => 'Parish Priest', 'category' => 'institution'],
            ['name' => 'Assistant Parish Priest', 'category' => 'institution'],
        ];

        foreach ($roleTypes as $roleType) {
            RoleType::create($roleType);
        }
    }
} 