<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Jesuit;
use App\Models\JesuitFormation;
use App\Models\FormationStage;
use App\Models\Institution;
use App\Models\Province;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use App\Models\JesuitHistory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class JesuitsSeeder extends Seeder
{
    public function run(): void
    {
        $formationStages = FormationStage::all();
        $provinces = Province::all();
        
        foreach ($provinces as $province) {
            // Calculate number of jesuits for this province (20-40)
            $totalProvincialJesuits = rand(30, 50);
            $formationCount = (int)($totalProvincialJesuits * 0.3); // 30% in formation
            $regularCount = $totalProvincialJesuits - $formationCount; // 70% regular
            
            $this->createJesuitsForProvince($province, $regularCount, $formationCount, $formationStages);
            
            // Handle jesuits for regions within this province
            $regions = Region::where('province_id', $province->id)->get();
            foreach ($regions as $region) {
                // Calculate number of jesuits for this region (10-25)
                $totalRegionalJesuits = rand(20, 40);
                $regionFormationCount = (int)($totalRegionalJesuits * 0.3); // 30% in formation
                $regionRegularCount = $totalRegionalJesuits - $regionFormationCount; // 70% regular
                
                $this->createJesuitsForRegion($region, $regionRegularCount, $regionFormationCount, $formationStages);
            }
        }
        
        // Handle common houses
        $this->populateCommonHouses();
        
        // Create 5-10 Jesuits in external locations
        $this->createExternalJesuits();
        
        // Create a few jesuits in a foreign province
        $this->createForeignProvinceJesuits();
    }
    
    private function createJesuitsForProvince($province, $regularCount, $formationCount, $formationStages)
    {
        // Get communities from this province (excluding regions)
        $regularCommunities = Community::where('province_id', $province->id)
                            ->whereNull('region_id')
                            ->where('is_formation_house', false)
                            ->where('is_attached_house', false)
                            ->where('is_common_house', false)
                            ->get();
        
        $formationHouses = Community::where('province_id', $province->id)
                            ->whereNull('region_id')
                            ->where('is_formation_house', true)
                            ->get();
                            
        // Distribute regular jesuits across regular communities
        if ($regularCommunities->count() > 0) {
            $jesuitPerCommunity = max(3, ceil($regularCount / $regularCommunities->count()));
            
            foreach ($regularCommunities as $community) {
                // Create a superior for each community
                $this->createSuperior($community);
                
                // Create 2-4 more priests/brothers per community
                $count = min($jesuitPerCommunity - 1, $regularCount);
                $regularCount -= $count;
                
                for ($i = 0; $i < $count; $i++) {
                    $this->createJesuitForCommunity($community, $province);
                }
            }
        }
        
        // Distribute formation jesuits across formation houses
        if ($formationHouses->count() > 0 && $formationCount > 0) {
            $formationPerHouse = ceil($formationCount / $formationHouses->count());
            
            foreach ($formationHouses as $formationHouse) {
                // Create a director for the formation house
                $this->createFormationDirector($formationHouse);
                
                // Create formation jesuits
                $count = min($formationPerHouse, $formationCount);
                $formationCount -= $count;
                
                for ($i = 0; $i < $count; $i++) {
                    $this->createFormationJesuit($formationHouse, $province, $formationStages);
                }
            }
        }
    }
    
    private function createJesuitsForRegion($region, $regularCount, $formationCount, $formationStages)
    {
        // Get communities from this region
        $regularCommunities = Community::where('region_id', $region->id)
                            ->where('is_formation_house', false)
                            ->where('is_attached_house', false)
                            ->where('is_common_house', false)
                            ->get();
        
        $formationHouses = Community::where('region_id', $region->id)
                            ->where('is_formation_house', true)
                            ->get();
        
        // Distribute regular jesuits across regular communities
        if ($regularCommunities->count() > 0) {
            $jesuitPerCommunity = max(3, ceil($regularCount / $regularCommunities->count()));
            
            foreach ($regularCommunities as $community) {
                // Create a superior for each community
                $this->createSuperior($community);
                
                // Create 2-4 more priests/brothers per community
                $count = min($jesuitPerCommunity - 1, $regularCount);
                $regularCount -= $count;
                
                for ($i = 0; $i < $count; $i++) {
                    $jesuit = Jesuit::factory()->create([
                        'current_community_id' => $community->id,
                        'province_id' => $region->province_id,
                        'region_id' => $region->id,
                    ]);
                    
                    $this->assignToInstitution($jesuit, $community);
                }
            }
        }
        
        // Distribute formation jesuits across formation houses
        if ($formationHouses->count() > 0 && $formationCount > 0) {
            $formationPerHouse = ceil($formationCount / $formationHouses->count());
            
            foreach ($formationHouses as $formationHouse) {
                // Create a director for the formation house
                $this->createFormationDirector($formationHouse);
                
                // Create formation jesuits
                $count = min($formationPerHouse, $formationCount);
                $formationCount -= $count;
                
                for ($i = 0; $i < $count; $i++) {
                    $jesuit = Jesuit::factory()->formation()->create([
                        'current_community_id' => $formationHouse->id,
                        'province_id' => $region->province_id,
                        'region_id' => $region->id,
                    ]);
                    
                    $this->assignToFormation($jesuit, $formationStages);
                }
            }
        }
    }
    
    // Helper methods
    private function createSuperior($community)
    {
        $superior = Jesuit::factory()->create([
            'current_community_id' => $community->id,
            'province_id' => $community->province_id,
            'region_id' => $community->region_id,
            'category' => 'P',  // Superior should be a priest
        ]);
        
        JesuitHistory::create([
            'jesuit_id' => $superior->id,
            'community_id' => $community->id,
            'province_id' => $community->province_id,
            'category' => 'P',
            'start_date' => Carbon::now()->subYears(rand(1, 5)),
            'status' => 'Superior',
        ]);
        
        return $superior;
    }
    
    private function createFormationDirector($formationHouse)
    {
        $director = Jesuit::factory()->create([
            'current_community_id' => $formationHouse->id,
            'province_id' => $formationHouse->province_id,
            'region_id' => $formationHouse->region_id,
            'category' => 'P',
        ]);
        
        JesuitHistory::create([
            'jesuit_id' => $director->id,
            'community_id' => $formationHouse->id,
            'province_id' => $formationHouse->province_id,
            'category' => 'P',
            'start_date' => Carbon::now()->subYears(rand(1, 5)),
            'status' => 'Superior / Formation Director',
        ]);
        
        return $director;
    }
    
    private function createJesuitForCommunity($community, $province)
    {
        $jesuit = Jesuit::factory()->create([
            'current_community_id' => $community->id,
            'province_id' => $community->province_id,
            'region_id' => $community->region_id,
        ]);
        
        $this->assignToInstitution($jesuit, $community);
        
        return $jesuit;
    }
    
    private function createFormationJesuit($formationHouse, $province, $formationStages)
    {
        $jesuit = Jesuit::factory()->formation()->create([
            'current_community_id' => $formationHouse->id,
            'province_id' => $formationHouse->province_id,
            'region_id' => $formationHouse->region_id,
        ]);
        
        $this->assignToFormation($jesuit, $formationStages);
        
        return $jesuit;
    }
    
    private function assignToInstitution($jesuit, $community)
    {
        $institutions = Institution::where('community_id', $community->id)->get();
        if ($institutions->count() > 0) {
            $institution = $institutions->random();
            
            JesuitHistory::create([
                'jesuit_id' => $jesuit->id,
                'community_id' => $community->id,
                'province_id' => $community->province_id,
                'region_id' => $community->region_id,
                'category' => $jesuit->category,
                'start_date' => Carbon::now()->subYears(rand(1, 5)),
                'status' => $this->getRandomRole($institution->type),
                'remarks' => "Works at {$institution->name} ({$institution->type})"
            ]);
        }
    }
    
    private function assignToFormation($jesuit, $formationStages)
    {
        $formationStage = $formationStages->random();
        JesuitFormation::create([
            'jesuit_id' => $jesuit->id,
            'formation_stage_id' => $formationStage->id,
            'start_date' => Carbon::now()->subYears(rand(1, 3)),
            'current_year' => rand(1, 3),
            'status' => 'active'
        ]);
        
        JesuitHistory::create([
            'jesuit_id' => $jesuit->id,
            'community_id' => $jesuit->current_community_id,
            'province_id' => $jesuit->province_id,
            'region_id' => $jesuit->region_id,
            'category' => $jesuit->category,
            'start_date' => Carbon::now()->subYears(rand(1, 3)),
            'status' => 'In Formation',
            'remarks' => "Formation stage: {$formationStage->name}"
        ]);
    }
    
    private function getRandomRole($institutionType)
    {
        switch(strtolower($institutionType)) {
            case 'school':
                $roles = ['Principal', 'Teacher', 'Administrator'];
                return $roles[array_rand($roles)];
            case 'college':
            case 'university':
                $roles = ['Professor', 'Dean', 'Administrator'];
                return $roles[array_rand($roles)];
            case 'parish':
                $roles = ['Parish Priest', 'Assistant Parish Priest'];
                return $roles[array_rand($roles)];
            case 'social_centre':
                $roles = ['Director', 'Coordinator', 'Worker'];
                return $roles[array_rand($roles)];
            default:
                $roles = ['Director', 'Administrator', 'Worker'];
                return $roles[array_rand($roles)];
        }
    }
    
    private function populateCommonHouses()
    {
        $commonHouses = Community::where('is_common_house', true)->get();
        
        foreach ($commonHouses as $commonHouse) {
            // Create a superior
            $superior = Jesuit::factory()->create([
                'current_community_id' => $commonHouse->id,
                'province_id' => Province::first()->id,
                'region_id' => null,
                'category' => 'P',
            ]);
            
            JesuitHistory::create([
                'jesuit_id' => $superior->id,
                'community_id' => $commonHouse->id,
                'assistancy_id' => $commonHouse->assistancy_id,
                'province_id' => Province::first()->id,
                'category' => 'P',
                'start_date' => Carbon::now()->subYears(rand(1, 5)),
                'status' => 'Superior',
            ]);
            
            // Create 3-5 more Jesuits from different provinces
            $additionalJesuits = rand(3, 5);
            for ($i = 0; $i < $additionalJesuits; $i++) {
                $jesuit = Jesuit::factory()->inForeignProvince()->create([
                    'current_community_id' => $commonHouse->id,
                ]);
                
                JesuitHistory::create([
                    'jesuit_id' => $jesuit->id,
                    'community_id' => $commonHouse->id,
                    'assistancy_id' => $commonHouse->assistancy_id,
                    'province_id' => $jesuit->province_id,
                    'category' => $jesuit->category,
                    'start_date' => Carbon::now()->subYears(rand(1, 3)),
                    'status' => 'Resident',
                ]);
            }
        }
    }
    
    private function createExternalJesuits()
    {
        $externalJesuits = rand(5, 10);
        for ($i = 0; $i < $externalJesuits; $i++) {
            $jesuit = Jesuit::factory()->inExternalLocation()->create();
            
            JesuitHistory::create([
                'jesuit_id' => $jesuit->id,
                'community_id' => null,
                'province_id' => $jesuit->province_id,
                'region_id' => $jesuit->region_id,
                'category' => $jesuit->category,
                'start_date' => Carbon::now()->subYears(rand(1, 3)),
                'status' => 'External Location',
                'remarks' => $jesuit->notes
            ]);
        }
    }
    
    private function createForeignProvinceJesuits()
    {
        $provinces = Province::all();
        if ($provinces->count() < 2) return;
        
        foreach ($provinces as $homeProvince) {
            // Find a foreign province
            $foreignProvince = Province::where('id', '!=', $homeProvince->id)->inRandomOrder()->first();
            if (!$foreignProvince) continue;
            
            // Find a community in the foreign province
            $foreignCommunity = Community::where('province_id', $foreignProvince->id)
                                ->where('is_formation_house', false)
                                ->inRandomOrder()
                                ->first();
            if (!$foreignCommunity) continue;
            
            // Create a jesuit from home province in a foreign province
            $jesuit = Jesuit::factory()->create([
                'current_community_id' => $foreignCommunity->id,
                'province_id' => $homeProvince->id,
                'region_id' => null,
                'prefix_modifier' => '+', // Indicator of being in foreign province
            ]);
            
            // Find a home community to attach to
            $homeCommunity = Community::where('province_id', $homeProvince->id)
                            ->whereNull('region_id')
                            ->inRandomOrder()
                            ->first();
            
            JesuitHistory::create([
                'jesuit_id' => $jesuit->id,
                'community_id' => $foreignCommunity->id,
                'province_id' => $homeProvince->id, // Original province
                'category' => $jesuit->category,
                'start_date' => Carbon::now()->subYears(rand(1, 3)),
                'status' => 'Foreign Assignment',
                'remarks' => "From {$homeProvince->name} province, currently in {$foreignProvince->name} province." . 
                            ($homeCommunity ? " Attached to {$homeCommunity->name}." : "")
            ]);
        }
    }
} 