<?php

namespace Database\Seeders;

use App\Models\{User, Community, Jesuit, RoleType, JesuitHistory};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateRelationshipsSeeder extends Seeder
{
    public function run(): void
    {
        // Login as superadmin
        $superadmin = User::where('email', 'melwilsj@jesuits.net')->first();
        if ($superadmin) {
            Auth::login($superadmin);
        } else {
            // Log error and exit if superadmin not found
            Log::error('Superadmin not found, cannot proceed with UpdateRelationshipsSeeder');
            return;
        }

        // Get superior role types from the database
        $superiorRoleTypes = RoleType::whereIn('name', ['Superior', 'Rector', 'Director'])->pluck('id')->toArray();
        
        // First handle regular communities
        $jesuits = Jesuit::whereNull('current_community_id')->get();
        foreach ($jesuits as $jesuit) {
            // Find a community in the Jesuit's province/region
            $community = null;
            
            if ($jesuit->region_id) {
                $community = Community::where('region_id', $jesuit->region_id)
                    ->where('is_common_house', false)
                    ->inRandomOrder()
                    ->first();
            } else if ($jesuit->province_id) {
                $community = Community::where('province_id', $jesuit->province_id)
                    ->whereNull('region_id')  // Direct province community
                    ->where('is_common_house', false)
                    ->inRandomOrder()
                    ->first();
            }
            
            if ($community) {
                $this->assignJesuitToCommunity($jesuit, $community);
            }
        }

        // Handle common house assignments separately
        Community::where('is_common_house', true)->get()->each(function ($community) use ($superiorRoleTypes) {
            // For common houses, get any available Jesuit not already a superior
            $jesuit = Jesuit::where('category', 'P')
                ->whereDoesntHave('roleAssignments', function($query) use ($superiorRoleTypes) {
                    $query->where('is_active', true)
                        ->whereHasMorph('assignable', [Community::class])
                        ->whereHas('roleType', function($q) use ($superiorRoleTypes) {
                            $q->whereIn('id', $superiorRoleTypes);
                        });
                })
                ->inRandomOrder()
                ->first();
            
            if ($jesuit) {
                $this->assignJesuitToCommunity($jesuit, $community);
                // Find the Superior role type from database
                $superiorRoleType = RoleType::where('name', 'Superior')->first();
                if ($superiorRoleType) {
                    $jesuit->roleAssignments()->create([
                        'role_type_id' => $superiorRoleType->id,
                        'assignable_type' => Community::class,
                        'assignable_id' => $community->id,
                        'start_date' => now(),
                        'is_active' => true,
                    ]);
                }
            }
        });

        // Verify all communities have superiors
        $communitiesWithoutSuperiors = Community::whereDoesntHave('roleAssignments', function($query) use ($superiorRoleTypes) {
            $query->where('is_active', true)
                ->whereHas('roleType', function($q) use ($superiorRoleTypes) {
                    $q->whereIn('id', $superiorRoleTypes);
                });
        })->get();
        
        foreach ($communitiesWithoutSuperiors as $community) {
            // Find an eligible priest in this community
            $priest = Jesuit::where('current_community_id', $community->id)
                    ->where('category', 'P')
                    ->inRandomOrder()
                    ->first();
            
            if ($priest) {
                // Find the appropriate superior role
                $roleName = $community->is_formation_house ? 'Rector' : 'Superior';
                $roleType = RoleType::where('name', $roleName)->first();
                
                if ($roleType) {
                    $priest->roleAssignments()->create([
                        'role_type_id' => $roleType->id,
                        'assignable_type' => Community::class,
                        'assignable_id' => $community->id,
                        'start_date' => now(),
                        'is_active' => true,
                    ]);
                }
            }
        }

        // Logout after we're done
        Auth::logout();
    }

    private function assignJesuitToCommunity(Jesuit $jesuit, Community $community): void
    {
        $jesuit->update(['current_community_id' => $community->id]);
        
        JesuitHistory::create([
            'jesuit_id' => $jesuit->id,
            'community_id' => $community->id,
            'province_id' => $community->isCommonHouse() ? null : $jesuit->province_id,
            'region_id' => $jesuit->region_id, // Make sure region_id is included
            'assistancy_id' => $community->isCommonHouse() ? $community->assistancy_id : null,
            'category' => $jesuit->category,
            'start_date' => now(),
            'status' => 'Member',
            'remarks' => 'Initial assignment'
        ]);
    }
} 