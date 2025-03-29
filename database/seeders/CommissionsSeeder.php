<?php

namespace Database\Seeders;

use App\Models\Commission;
use App\Models\Jesuit;
use App\Models\Province;
use App\Models\Region;
use App\Models\RoleType;
use App\Models\RoleAssignment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CommissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create the required role types
        $headRoleType = RoleType::firstOrCreate(['name' => 'Commission Head']);
        $memberRoleType = RoleType::firstOrCreate(['name' => 'Commission Member']);

        // Associative array mapping commission codes to names
        $commissions = [
            'CBF' => 'Building & Finance',
            'CFVP' => 'Formation and Vocation Promotion',
            'CSF' => 'Service of Faith',
            'CSE' => 'Secondary Education',
            'CHE' => 'Higher Education',
            'CEJ' => 'Ecology & Justice',
            'CYM' => 'Youth Ministry',
            'CAA' => 'Alumni/ae Associations',
            'CPWPN' => 'Pope\'s Worldwide Prayer Network / Eucharistic Youth Movement',
            'CMA' => 'Media Apostolate'
        ];
        
        $provinces = Province::all();
        $assignedHeads = []; // Track jesuits already assigned as heads
        
        foreach ($provinces as $province) {
            $priests = Jesuit::where('province_id', $province->id)
                            ->whereNull('region_id')
                            ->where('category', 'P')
                            ->where('is_active', true)
                            ->whereNotIn('id', $assignedHeads) // Exclude already assigned heads
                            ->get();
                            
            if ($priests->count() < 7) {
                continue; // Skip if not enough priests
            }
            
            foreach ($commissions as $code => $name) {
                // Create unique code using province code + commission code
                $uniqueCode = $province->code . $code;
                
                // Check if commission already exists
                $commission = Commission::where('code', $uniqueCode)->first();
                if (!$commission) {
                    $commission = Commission::create([
                        'name' => $name,
                        'code' => $uniqueCode,
                        'province_id' => $province->id,
                        'description' => "Provincial Commission for $name",
                        'is_active' => true
                    ]);
                }
                
                // Check if this commission already has an active head
                $hasHead = RoleAssignment::where('assignable_type', Commission::class)
                    ->where('assignable_id', $commission->id)
                    ->where('role_type_id', $headRoleType->id)
                    ->where('is_active', true)
                    ->exists();
                
                // Assign 3-5 members to each commission
                $memberCount = min($priests->count(), rand(3, 5));
                $selectedPriests = $priests->random($memberCount);
                
                foreach ($selectedPriests as $index => $priest) {
                    // Skip if trying to add a head but one already exists
                    if ($index === 0 && $hasHead) {
                        continue;
                    }
                    
                    // Check if this priest is already a member of this commission
                    $existingAssignment = RoleAssignment::where('assignable_type', Commission::class)
                        ->where('assignable_id', $priest->id)
                        ->where('jesuit_id', $priest->id)
                        ->where('is_active', true)
                        ->exists();
                        
                    if (!$existingAssignment) {
                        RoleAssignment::create([
                            'jesuit_id' => $priest->id,
                            'role_type_id' => $index === 0 ? $headRoleType->id : $memberRoleType->id,
                            'assignable_type' => Commission::class,
                            'assignable_id' => $index === 0 ? $commission->id : $priest->id,
                            'start_date' => Carbon::now()->subYears(rand(0, 3)),
                            'end_date' => Carbon::now()->addYears(rand(1, 3)),
                            'is_active' => true
                        ]);
                        
                        // Track this priest if they're a head
                        if ($index === 0) {
                            $assignedHeads[] = $priest->id;
                        }
                    }
                }
            }
            
            // Create commissions for each region in this province
            $regions = Region::where('province_id', $province->id)->get();
            foreach ($regions as $region) {
                // Get priests from this region
                $regionPriests = Jesuit::where('region_id', $region->id)
                                    ->where('category', 'P')
                                    ->where('is_active', true)
                                    ->get();
                                    
                if ($regionPriests->count() < 5) {
                    continue; // Skip if not enough priests
                }
                
                foreach ($commissions as $code => $name) {
                    // Create unique code using region code + commission code
                    $uniqueCode = $region->code . $code;
                    
                    // Check if commission already exists
                    $commission = Commission::where('code', $uniqueCode)->first();
                    if (!$commission) {
                        $commission = Commission::create([
                            'name' => $name,
                            'code' => $uniqueCode,
                            'province_id' => $province->id, // Province is still parent
                            'region_id' => $region->id, // But specific to region
                            'description' => "Regional Commission for $name - {$region->name}",
                            'is_active' => true
                        ]);
                    }
                    
                    // Check if this commission already has an active head
                    $hasHead = RoleAssignment::where('assignable_type', Commission::class)
                        ->where('assignable_id', $commission->id)
                        ->where('role_type_id', $headRoleType->id)
                        ->where('is_active', true)
                        ->exists();
                    
                    // Assign 3-4 members to each commission
                    $memberCount = min($regionPriests->count(), rand(3, 4));
                    $selectedPriests = $regionPriests->random($memberCount);
                    
                    foreach ($selectedPriests as $index => $priest) {
                        // Skip if trying to add a head but one already exists
                        if ($index === 0 && $hasHead) {
                            continue;
                        }
                        
                        // Check if this priest is already a member of this commission
                        $existingAssignment = RoleAssignment::where('assignable_type', Commission::class)
                            ->where('assignable_id', $priest->id)
                            ->where('jesuit_id', $priest->id)
                            ->where('is_active', true)
                            ->exists();
                            
                        if (!$existingAssignment) {
                            RoleAssignment::create([
                                'jesuit_id' => $priest->id,
                                'role_type_id' => $index === 0 ? $headRoleType->id : $memberRoleType->id,
                                'assignable_type' => Commission::class,
                                'assignable_id' => $index === 0 ? $commission->id : $priest->id,
                                'start_date' => Carbon::now()->subYears(rand(0, 3)),
                                'end_date' => Carbon::now()->addYears(rand(1, 3)),
                                'is_active' => true
                            ]);
                            
                            // Track this priest if they're a head
                            if ($index === 0) {
                                $assignedHeads[] = $priest->id;
                            }
                        }
                    }
                }
            }
        }
    }
} 