<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Jesuit;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Http\Resources\ProvinceDataResource;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Community;
use Illuminate\Support\Facades\Cache;
use App\Constants\RoleTypes;

class ProvinceDataController extends BaseController
{
    public function getProvinceJesuitsData(Request $request)
    {
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        // Get the requester's province and region
        $requestorProvinceId = $jesuit->province_id;
        $requestorRegionId = $jesuit->region_id;

        // Query all Jesuits from the same province
        $jesuits = Jesuit::with([
            'currentCommunity:id,name,code',
            'activeRoles' => function($query) {
                $query->where('is_active', true)->whereNull('end_date');
            },
            'activeRoles.roleType:id,name',
            'province:id,name,code',
            'region:id,name,code',
            'user:id,name,email,phone_number'
        ])
        ->where('province_id', $requestorProvinceId)
        ->where('is_active', true)
        ->get();

        // Transform data based on requester's context
        $transformedJesuits = $jesuits->map(function ($jesuit) use ($requestorRegionId) {
            $data = [
                'id' => $jesuit->id,
                'user_id' => $jesuit->user_id,
                'name' => $jesuit->user->name,
                'code' => $jesuit->code,
                'category' => $jesuit->category,
                'photo_url' => $jesuit->photo_url,
                'email' => $jesuit->user->email,
                'phone_number' => $jesuit->user->phone_number,
                'dob' => $jesuit->dob,
                'joining_date' => $jesuit->joining_date,
                'priesthood_date' => $jesuit->priesthood_date,
                'final_vows_date' => $jesuit->final_vows_date,
                'academic_qualifications' => $jesuit->academic_qualifications,
                'is_external' => $jesuit->is_external,
                'notes' => $jesuit->notes,
                // Add relationship flags
                'province_only' => !$jesuit->region_id,
                'province_id' => $jesuit->province_id,
                'region_id' => $jesuit->region_id,

                
                // Current community
                'current_community_id' => $jesuit->currentCommunity->id,
                'current_community' => $jesuit->currentCommunity->name,
                'province' => $jesuit->province->code,
                'region' => $jesuit->region? $jesuit->region->code:null,
                
                // Active roles
                'roles' => $jesuit->activeRoles->map(function($role) {
                    return [
                        'type' => $role->roleType->name,
                        'institution' => $role->assignable->name ?? null,
                    ];
                }),
            ];

            // Add special flags based on context
            if ($requestorRegionId) {
                // Requester is from region
                $data['province_only'] = ($jesuit->region_id !== $requestorRegionId);
            } else {
                // Requester is from province
                $data['region_member'] = (bool)$jesuit->region_id;
            }

            return $data;
        });

        // Group data by regions and province
        $provinceData = [
            'province' => [
                'id' => $jesuit->province->id,
                'name' => $jesuit->province->name,
                'code' => $jesuit->province->code,
            ],
            'regions' => $jesuit->province->regions->map(function($region) {
                return [
                    'id' => $region->id,
                    'name' => $region->name,
                    'code' => $region->code,
                ];
            }),
            'members' => $transformedJesuits,
        ];

        $cacheKey = "province_data_{$requestorProvinceId}";
        $cacheDuration = now()->addMinutes(30);

        return $this->successResponse(Cache::remember($cacheKey, $cacheDuration, function() use ($provinceData) {
            return $provinceData;
        }));
    }

    public function getProvinceCommunitiesData(Request $request)
    {
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        $requestorProvinceId = $jesuit->province_id;
        $requestorRegionId = $jesuit->region_id;

        $communities = Community::with([
            'institutions:id,community_id,name,type,address,contact_details,diocese,district,state',
            'roleAssignments' => function($query) {
                $query->where('is_active', true)
                    ->whereHas('roleType', function($q) {
                        $q->whereIn('name', RoleTypes::SUPERIOR_ROLES);
                    })
                    ->with(['jesuit.user:id,name,phone_number']);
            },
            'province:id,name,code',
            'region:id,name,code',
            'assistancy:id,name'
        ])
        ->where(function($query) use ($requestorProvinceId) {
            $query->where('province_id', $requestorProvinceId)
                  ->orWhereHas('region', function($q) use ($requestorProvinceId) {
                      $q->where('province_id', $requestorProvinceId);
                  });
        })
        ->where('is_active', true)
        ->get();

        $transformedCommunities = $communities->map(function($community) use ($requestorRegionId) {
            $data = $community->toArray();
            $data['province_only'] = !$community->region_id;
            $data['region_member'] = $community->region_id === $requestorRegionId;
            $data['common_house'] = $community->is_common_house;
            
            // Get superior from role assignments
            $superiorAssignment = $community->roleAssignments->first();
            $data['superior'] = $superiorAssignment ? [
                'id' => $superiorAssignment->jesuit->id,
                'name' => $superiorAssignment->jesuit->user->name,
                'phone_number' => $superiorAssignment->jesuit->user->phone_number
            ] : null;
            
            // Remove the role assignments from the response
            unset($data['role_assignments']);
            
            return $data;
        });

        return $this->successResponse($transformedCommunities);
    }
} 