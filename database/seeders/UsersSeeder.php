<?php

namespace Database\Seeders;

use App\Models\{User, Jesuit, Province, Community, RoleType};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user if doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'melwilsj@jesuits.net'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'type' => 'admin',
                'is_active' => true
            ]
        );

        // Create Jesuit record for admin
        $adminJesuit = Jesuit::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'province_id' => Province::first()->id,
                'code' => 'ADM001',
                'category' => 'P',
                'dob' => now()->subYears(40),
                'joining_date' => now()->subYears(20),
                'priesthood_date' => now()->subYears(10),
                'status' => 'active',
                'is_active' => true
            ]
        );

        // Assign provincial role to admin's jesuit record
        if (!$adminJesuit->roleAssignments()->where('role_type_id', RoleType::where('name', 'Provincial')->first()->id)->exists()) {
            $adminJesuit->roleAssignments()->create([
                'role_type_id' => RoleType::where('name', 'Provincial')->first()->id,
                'assignable_type' => Province::class,
                'assignable_id' => Province::first()->id,
                'start_date' => now(),
                'is_active' => true,
            ]);
        }

        // Create province admins
        Province::all()->each(function ($province) {
            $email = 'admin.' . strtolower($province->code) . '@jesuits.net';
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $province->name . ' Admin',
                    'password' => Hash::make('password'),
                    'type' => 'admin',
                    'is_active' => true
                ]
            );

            // Create Jesuit record for province admin
            $provinceJesuit = Jesuit::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'province_id' => $province->id,
                    'code' => 'PA' . $province->code,
                    'category' => 'P',
                    'dob' => now()->subYears(rand(35, 50)),
                    'joining_date' => now()->subYears(rand(15, 30)),
                    'priesthood_date' => now()->subYears(rand(5, 15)),
                    'status' => 'active',
                    'is_active' => true
                ]
            );

            // Assign provincial role
            if (!$provinceJesuit->roleAssignments()->where('role_type_id', RoleType::where('name', 'Provincial')->first()->id)->exists()) {
                $provinceJesuit->roleAssignments()->create([
                    'role_type_id' => RoleType::where('name', 'Provincial')->first()->id,
                    'assignable_type' => Province::class,
                    'assignable_id' => $province->id,
                    'start_date' => now(),
                    'is_active' => true,
                ]);
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