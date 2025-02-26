<?php

namespace Database\Seeders;

use App\Models\{User, Jesuit, Province, Community, RoleType, Assistancy, Role};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
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
        $superadmin->roles()->syncWithoutDetaching([$superadminRole->id]);

        // Create POSA user
        $posaUser = User::firstOrCreate(
            ['email' => 'sjipsb1@gmail.com'],
            [
                'name' => 'POSA',
                'password' => Hash::make('password'),
                'type' => 'admin',
                'is_active' => true
            ]
        );

        // Create Jesuit record for POSA
        $posaJesuit = Jesuit::firstOrCreate(
            ['user_id' => $posaUser->id],
            [
                'province_id' => Province::first()->id,
                'code' => 'POSA001',
                'category' => 'P',
                'dob' => now()->subYears(50),
                'joining_date' => now()->subYears(30),
                'priesthood_date' => now()->subYears(20),
                'status' => 'active',
                'is_active' => true
            ]
        );

        // Assign POSA role
        $posaRoleType = RoleType::where('name', 'POSA')->first();
        if (!$posaJesuit->roleAssignments()->where('role_type_id', $posaRoleType->id)->exists()) {
            $posaJesuit->roleAssignments()->create([
                'role_type_id' => $posaRoleType->id,
                'assignable_type' => Assistancy::class,
                'assignable_id' => Assistancy::first()->id,
                'start_date' => now(),
                'is_active' => true,
            ]);
        }

        // Create one Provincial and two admins for each province
        Province::all()->each(function ($province) {
            // Create Provincial
            // Override email for first province's Provincial
            if ($province->id === Province::first()->id) {
                $provincialEmail = 'sjipsb7@gmail.com';
            } else {
                $provincialEmail = 'provincial.' . strtolower($province->code) . '@jesuits.net';
            }
            $provincialUser = User::firstOrCreate(
                ['email' => $provincialEmail],
                [
                    'name' => $province->name . ' Provincial',
                    'password' => Hash::make('password'),
                    'type' => 'admin',
                    'is_active' => true
                ]
            );

            // Create Jesuit record for Provincial
            $provincialJesuit = Jesuit::firstOrCreate(
                ['user_id' => $provincialUser->id],
                [
                    'province_id' => $province->id,
                    'code' => 'PR' . $province->code,
                    'category' => 'P',
                    'dob' => now()->subYears(45),
                    'joining_date' => now()->subYears(25),
                    'priesthood_date' => now()->subYears(15),
                    'status' => 'active',
                    'is_active' => true
                ]
            );

            // Assign Provincial role
            $provincialRoleType = RoleType::where('name', 'Provincial')->first();
            if (!$provincialJesuit->roleAssignments()->where('role_type_id', $provincialRoleType->id)->exists()) {
                $provincialJesuit->roleAssignments()->create([
                    'role_type_id' => $provincialRoleType->id,
                    'assignable_type' => Province::class,
                    'assignable_id' => $province->id,
                    'start_date' => now(),
                    'is_active' => true,
                ]);
            }

            // Create province admins
            for ($i = 1; $i <= 2; $i++) {
                $email = 'admin' . $i . '.' . strtolower($province->code) . '@jesuits.net';
                
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $province->name . ' Admin ' . $i,
                        'password' => Hash::make('password'),
                        'type' => 'admin',
                        'is_active' => true
                    ]
                );

                // Assign province_admin role
                $provinceAdminRole = Role::where('slug', 'province_admin')->first();
                $user->roles()->syncWithoutDetaching([$provinceAdminRole->id]);

                // Create Jesuit record for province admin
                Jesuit::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'province_id' => $province->id,
                        'code' => 'PA' . $province->code . $i,
                        'category' => 'P',
                        'dob' => now()->subYears(rand(35, 50)),
                        'joining_date' => now()->subYears(rand(15, 30)),
                        'priesthood_date' => now()->subYears(rand(5, 15)),
                        'status' => 'active',
                        'is_active' => true
                    ]
                );
            }
        });

        // Create regular users
        if (User::count() < 15) {
            User::factory(15 - User::count())
                ->create(['type' => 'jesuit'])
                ->each(function ($user) {
                    // Create Jesuit record
                    $jesuit = Jesuit::factory()->create(['user_id' => $user->id]);
                    
                    // Create initial history record
                    $jesuit->histories()->create([
                        'community_id' => $jesuit->current_community_id,
                        'province_id' => $jesuit->province_id,
                        'category' => $jesuit->category,
                        'start_date' => now()->subMonths(rand(1, 24)),
                        'status' => 'active'
                    ]);
                });
        }
    }
} 