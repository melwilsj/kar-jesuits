<?php

namespace Database\Seeders;

use App\Models\{User, Province, Community, Role, RoleType};
use Database\Factories\RoleAssignmentFactory;
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
                'type' => 'P',
            ]
        );

        // Assign superadmin role through role_assignments if not already assigned
        if (!$admin->roleAssignments()->where('role_type_id', RoleType::where('name', 'Provincial')->first()->id)->exists()) {
            $admin->roleAssignments()->create([
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
                    'type' => 'P',
                    'province_id' => $province->id,
                ]
            );

            // Assign province admin role if not already assigned
            if (!$user->roleAssignments()->where('role_type_id', RoleType::where('name', 'Provincial')->first()->id)->exists()) {
                $user->roleAssignments()->create([
                    'role_type_id' => RoleType::where('name', 'Provincial')->first()->id,
                    'assignable_type' => Province::class,
                    'assignable_id' => $province->id,
                    'start_date' => now(),
                    'is_active' => true,
                ]);
            }
        });

        // Create regular users only if we have less than 15
        $userCount = User::count();
        if ($userCount < 15) { // 15 = 1 superadmin + 6 province admins + 8 regular users
            User::factory(15 - $userCount)->create()->each(function ($user) {
                // Assign random formation stage
                $user->formationHistory()->create([
                    'stage_id' => rand(1, 13),
                    'start_date' => now()->subMonths(rand(1, 24)),
                ]);

                // Create role assignments using factory
                $roleTypes = RoleType::inRandomOrder()->limit(rand(1, 3))->get();
                foreach ($roleTypes as $roleType) {
                    try {
                        $user->roleAssignments()->save(
                            RoleAssignmentFactory::new()->make([
                                'user_id' => $user->id,
                                'role_type_id' => $roleType->id
                            ])
                        );
                    } catch (\Exception $e) {
                        continue; // Skip if unique constraint fails
                    }
                }
            });
        }
    }
} 