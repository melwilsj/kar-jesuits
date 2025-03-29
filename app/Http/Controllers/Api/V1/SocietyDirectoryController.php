<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Assistancy;
use App\Models\Province;
use App\Models\Region;
use App\Models\Community;
use Illuminate\Http\Request;
use App\Constants\RoleTypes;

class SocietyDirectoryController extends BaseController
{
    /**
     * Get all assistancies
     */
    public function getAssistancies(Request $request)
    {
        $assistancies = Assistancy::with('provinces')->get();
        
        return $this->successResponse($assistancies);
    }
    
    /**
     * Get provinces by assistancy
     */
    public function getProvincesByAssistancy(Request $request, $assistancy_id)
    {
        $provinces = Province::where('assistancy_id', $assistancy_id)
            ->where('is_active', true)
            ->get();
            
        return $this->successResponse($provinces);
    }

    /**
     * Get regions by assistancy
     */
    public function getRegionsByAssistancy(Request $request, $assistancy_id)
    {
        $regions = Region::where('assistancy_id', $assistancy_id)
            ->where('is_active', true)
            ->get();
            
        return $this->successResponse($regions);
    }
    
    /**
     * Get communities by province code
     */
    public function getCommunitiesByProvince(Request $request, $code)
    {
        return $this->getCommunities($request, $code, 'province');
    }

    /**
     * Get communities by region code
     */
    public function getCommunitiesByRegion(Request $request, $code)
    {
        return $this->getCommunities($request, $code, 'region');
    }

    /**
     * Get communities by code and type
     */
    public function getCommunities(Request $request, $code, $type)
    {
        $province = null;
        if ($type == 'province') {
            $province = Province::where('code', $code)->first();
            if (!$province) {
                return $this->errorResponse('Province not found', [], 404);
            }
        } else if ($type == 'region') {
            $province = Region::where('code', $code)->first();
            if (!$province) {
                return $this->errorResponse('Region not found', [], 404);
            }
        }
        
        
        $communities = Community::with([
            'institutions:id,community_id,name,type,address',
            'roleAssignments' => function($query) {
                $query->where('is_active', true)
                    ->whereHas('roleType', function($q) {
                        $q->whereIn('name', RoleTypes::SUPERIOR_ROLES);
                    })
                    ->with(['jesuit.user:id,name,phone_number']);
            }
        ])
        ->when($type == 'province', function($query) use ($province) {
            return $query->where('province_id', $province->id)
                        ->whereNull('region_id');
        })
        ->when($type == 'region', function($query) use ($province) {
            return $query->where('region_id', $province->id);
        })
        ->where('is_active', true)
        ->get();
        
        return $this->successResponse([$communities]);
    }
} 