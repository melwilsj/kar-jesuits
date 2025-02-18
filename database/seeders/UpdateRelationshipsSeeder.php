<?php

namespace Database\Seeders;

use App\Models\{User, Community, Jesuit, RoleType, JesuitHistory};
use Illuminate\Database\Seeder;

class UpdateRelationshipsSeeder extends Seeder
{
    public function run(): void
    {
        // Assign communities to jesuits
        Jesuit::all()->each(function ($jesuit) {
            $community = Community::where('province_id', $jesuit->province_id)
                ->inRandomOrder()
                ->first();
            
            if ($community) {
                $jesuit->update(['current_community_id' => $community->id]);
                
                // Create history entry
                JesuitHistory::create([
                    'jesuit_id' => $jesuit->id,
                    'community_id' => $community->id,
                    'province_id' => $jesuit->province_id,
                    'category' => $jesuit->category,
                    'start_date' => now(),
                    'status' => 'Member',
                    'remarks' => 'Initial assignment'
                ]);
            }
        });

        // Assign superiors to communities
        Community::all()->each(function ($community) {
            $potentialSuperior = Jesuit::where('province_id', $community->province_id)
                ->where('current_community_id', $community->id)
                ->where('category', 'P')
                ->inRandomOrder()
                ->first();
            
            if ($potentialSuperior) {
                $community->assignSuperior(
                    $potentialSuperior, 
                    $community->superior_type ?? 'Superior'
                );
            }
        });
    }
} 