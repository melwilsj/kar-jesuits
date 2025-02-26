<?php

namespace Database\Seeders;

use App\Constants\RoleTypes;
use App\Models\{User, Community, Jesuit, RoleType, JesuitHistory};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class UpdateRelationshipsSeeder extends Seeder
{
    public function run(): void
    {
        // Login as superadmin
        $superadmin = User::where('email', 'melwilsj@jesuits.net')->first();
        Auth::login($superadmin);

        // First handle regular communities
        Jesuit::all()->each(function ($jesuit) {
            $community = Community::where('province_id', $jesuit->province_id)
                ->where('is_common_house', false)
                ->inRandomOrder()
                ->first();
            
            if ($community) {
                $this->assignJesuitToCommunity($jesuit, $community);
            }
        });

        // Handle common house assignments separately
        Community::where('is_common_house', true)->get()->each(function ($community) {
            // For common houses, get any available Jesuit not already a superior
            $jesuit = Jesuit::where('category', 'P')
                ->whereDoesntHave('roleAssignments', function($query) {
                    $query->where('is_active', true)
                        ->whereHasMorph('assignable', [Community::class])
                        ->whereHas('roleType', function($q) {
                            $q->whereIn('name', RoleTypes::SUPERIOR_ROLES);
                        });
                })
                ->inRandomOrder()
                ->first();
            
            if ($jesuit) {
                $this->assignJesuitToCommunity($jesuit, $community);
                $community->assignSuperior($jesuit, 'Superior');
            }
        });

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
            'assistancy_id' => $community->isCommonHouse() ? $community->assistancy_id : null,
            'category' => $jesuit->category,
            'start_date' => now(),
            'status' => 'Member',
            'remarks' => 'Initial assignment'
        ]);
    }
} 