<?php

namespace Database\Seeders;

use App\Models\Assistancy;
use App\Models\Community;
use App\Models\Institution;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;

class CommunitiesSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = Province::all();
        $assistancy = Assistancy::first();
        
        // For each province
        foreach ($provinces as $province) {
            $regions = Region::where('province_id', $province->id)->get();
            
            // Create 2-3 province-level communities (no region)
            $provinceCommunities = Community::factory()
                ->count(rand(3, 5))
                ->create([
                    'province_id' => $province->id,
                    'region_id' => null
                ]);
                
            // Add institutions to province communities
            foreach ($provinceCommunities as $community) {
                $this->createInstitutionsForCommunity($community, $province->id);
            }
            
            // For each region in this province
            foreach ($regions as $region) {
                // Create 2-3 region-level communities
                $regionCommunities = Community::factory()
                    ->count(rand(3, 5))
                    ->create([
                        'province_id' => $province->id,
                        'region_id' => $region->id
                    ]);
                    
                // Add institutions to region communities
                foreach ($regionCommunities as $community) {
                    $this->createInstitutionsForCommunity($community, $province->id);
                }
                
                // Create 1 formation house for the region
                Community::factory()
                    ->formationHouse()
                    ->create([
                        'province_id' => $province->id,
                        'region_id' => $region->id,
                        'name' => "Formation House - " . $region->name
                    ]);
            }
            
            // Create 1-2 formation houses per province (no region)
            Community::factory()
                ->formationHouse()
                ->count(rand(1, 2))
                ->create([
                    'province_id' => $province->id,
                    'region_id' => null,
                    'name' => "Formation House - " . $province->name
                ]);
        }
        
        // Create 2-3 common houses (no institutions needed)
        Community::factory()
            ->commonHouse()
            ->count(rand(2, 3))
            ->create([
                'assistancy_id' => $assistancy->id
            ]);
    }
    
    private function createInstitutionsForCommunity($community, $provinceId): void
    {
        $institutionTypes = ['school', 'college', 'university', 'hostel', 
            'community_college', 'iti', 'parish', 
            'social_center', 'farm', 'ngo', 'retreat_center', 'other'];
        
        // Create 2-3 institutions of different types for each community
        $selectedTypes = array_rand(array_flip($institutionTypes), rand(2, 3));
        foreach ($selectedTypes as $type) {
            Institution::factory()->create([
                'community_id' => $community->id,
                'type' => $type
            ]);
        }
        
        // Create 0-1 attached houses for each community (30% chance)
        if (rand(1, 10) <= 3) {
            $attachedHouse = Community::factory()
                ->attachedHouse()
                ->create([
                    'province_id' => $community->province_id,
                    'region_id' => $community->region_id,
                    'parent_community_id' => $community->id
                ]);
            
            // Add a parish or school to the attached house
            Institution::factory()->create([
                'community_id' => $attachedHouse->id,
                'type' => rand(0, 1) ? 'parish' : 'school'
            ]);
        }
    }
} 