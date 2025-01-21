<?php

namespace Database\Seeders;

use App\Models\{User, Community};
use Illuminate\Database\Seeder;

class UpdateRelationshipsSeeder extends Seeder
{
    public function run(): void
    {
        // Assign communities to users
        User::all()->each(function ($user) {
            $community = Community::where('province_id', $user->province_id)
                ->inRandomOrder()
                ->first();
            
            if ($community) {
                $user->update(['current_community_id' => $community->id]);
            }
        });

        // Assign superiors to communities
        Community::all()->each(function ($community) {
            $superior = User::where('province_id', $community->province_id)
                ->where('type', 'P')
                ->inRandomOrder()
                ->first();
            
            if ($superior) {
                $community->update(['superior_id' => $superior->id]);
            }
        });
    }
} 