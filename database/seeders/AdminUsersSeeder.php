<?php

namespace Database\Seeders;

use App\Models\{User, Jesuit, Province, Community, RoleType, Assistancy, Role};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create superadmin user if doesn't exist
        $superadmin = User::firstOrCreate(
            ['email' => 'melwilsj@jesuits.net'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'type' => 'superadmin',
                'is_active' => true
            ]
        );

        // Assign superadmin role
        $superadminRole = Role::where('slug', 'superadmin')->first();
        if ($superadminRole) {
            $superadmin->roles()->syncWithoutDetaching([$superadminRole->id]);
        }

        // Create POSA user
        $posaUser = User::firstOrCreate(
            ['email' => 'sjipsb1@gmail.com'],
            [
                'name' => 'POSA',
                'password' => Hash::make('password'),
                'type' => 'jesuit',
                'is_active' => true
            ]
        );

        // Create Jesuit record for POSA
        $province = Province::first();
        if ($province) {
            $posaJesuit = Jesuit::firstOrCreate(
                ['user_id' => $posaUser->id],
                [
                    'province_id' => $province->id,
                    'code' => 'POSA001',
                    'category' => 'P',
                    'dob' => now()->subYears(50),
                    'joining_date' => now()->subYears(30),
                    'priesthood_date' => now()->subYears(20),
                    'status' => 'active',
                    'is_active' => true
                ]
            );

            // Assign POSA role to the user
            $posaRole = Role::where('name', 'POSA')
                        ->orWhere('slug', strtolower('posa'))
                        ->first();
                        
            if ($posaRole) {
                $posaUser->roles()->syncWithoutDetaching([$posaRole->id]);
            }
            
            // Set POSA role assignment for the Assistancy
            $assistancy = Assistancy::first();
            $posaRoleType = RoleType::where('name', 'POSA')->first();
            
            if ($assistancy && $posaRoleType && $posaJesuit) {
                // Check if role assignment already exists
                if (!$posaJesuit->roleAssignments()->where('role_type_id', $posaRoleType->id)->exists()) {
                    $posaJesuit->roleAssignments()->create([
                        'role_type_id' => $posaRoleType->id,
                        'assignable_type' => Assistancy::class,
                        'assignable_id' => $assistancy->id,
                        'start_date' => now(),
                        'is_active' => true,
                    ]);
                }
            }

            // Create Provincial users for each province
            $provinces = Province::all();
            foreach ($provinces as $province) {
                // Find a suitable priest from this province to be Provincial
                $provincial = Jesuit::where('province_id', $province->id)
                                ->whereNull('region_id')  // Direct province member
                                ->where('category', 'P')
                                ->where('is_active', true)
                                ->inRandomOrder()
                                ->first();
                
                if (!$provincial) {
                    // If no suitable Jesuit found, create one
                    $provincialUser = User::factory()->create([
                        'name' => 'Provincial of ' . $province->name,
                        'email' => 'provincial.' . strtolower(str_replace(' ', '', $province->name)) . '@jesuits.net',
                        'type' => 'jesuit',
                        'is_active' => true
                    ]);
                    
                    $provincial = Jesuit::factory()->create([
                        'user_id' => $provincialUser->id,
                        'province_id' => $province->id,
                        'region_id' => null,
                        'category' => 'P',
                        'status' => 'active'
                    ]);
                }
                
                // Assign Provincial role
                $provincialRoleType = RoleType::where('name', 'Provincial')->first();
                if ($provincialRoleType && $provincial) {
                    // Check if role assignment already exists
                    if (!$provincial->roleAssignments()->where('role_type_id', $provincialRoleType->id)->exists()) {
                        $provincial->roleAssignments()->create([
                            'role_type_id' => $provincialRoleType->id,
                            'assignable_type' => Province::class,
                            'assignable_id' => $province->id,
                            'start_date' => now(),
                            'is_active' => true,
                        ]);
                    }
                }
                
                // Create Region Superiors for each region
                $regions = $province->regions;
                foreach ($regions as $region) {
                    $regionSuperior = Jesuit::where('region_id', $region->id)
                                        ->where('category', 'P')
                                        ->where('is_active', true)
                                        ->inRandomOrder()
                                        ->first();
                    
                    if ($regionSuperior) {
                        $regionSuperiorRoleType = RoleType::where('name', 'Region Superior')->first();
                        if ($regionSuperiorRoleType) {
                            // Check if role assignment already exists
                            if (!$regionSuperior->roleAssignments()->where('role_type_id', $regionSuperiorRoleType->id)->exists()) {
                                $regionSuperior->roleAssignments()->create([
                                    'role_type_id' => $regionSuperiorRoleType->id,
                                    'assignable_type' => Province\Region::class,
                                    'assignable_id' => $region->id,
                                    'start_date' => now(),
                                    'is_active' => true,
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
} 