<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Jesuit;
use Illuminate\Http\Request;

class JesuitFilterController extends BaseController
{
    /**
     * Transform Jesuit data to match client-side TypeScript interface
     *
     * @param Jesuit $jesuit
     * @param bool $requestorHasRegion
     * @return array
     */
    private function transformJesuit($jesuit, $requestorRegionId = null)
    {
        $data = [
            'id' => $jesuit->id,
            'user_id' => $jesuit->user_id,
            'name' => $jesuit->user->name,
            'code' => $jesuit->code,
            'category' => $jesuit->category,
            'photo_url' => $jesuit->photo_url,
            'phone_number' => $jesuit->user->phone_number,
            'email' => $jesuit->user->email,
            'dob' => $jesuit->dob,
            'joining_date' => $jesuit->joining_date,
            'priesthood_date' => $jesuit->priesthood_date,
            'final_vows_date' => $jesuit->final_vows_date,
            'academic_qualifications' => $jesuit->academic_qualifications,
            'is_external' => $jesuit->is_external,
            'notes' => $jesuit->notes,
            'province_only' => !$jesuit->region_id,
            'province_id' => $jesuit->province_id,
            'region_id' => $jesuit->region_id,
            'current_community_id' => $jesuit->current_community_id,
            'current_community' => $jesuit->currentCommunity ? $jesuit->currentCommunity->name : null,
            'province' => $jesuit->province ? $jesuit->province->code : null,
            'region' => $jesuit->region ? $jesuit->region->code : null,
            'roles' => $jesuit->activeRoles ? $jesuit->activeRoles->map(function($role) {
                return [
                    'type' => $role->roleType->name,
                    'institution' => $role->assignable->name ?? null,
                ];
            }) : [],
        ];

        // Add special flags based on context if requestor has region
        if ($requestorRegionId) {
            $data['province_only'] = ($jesuit->region_id !== $requestorRegionId);
        }

        return $data;
    }

    /**
     * Filter Jesuits by formation stage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function byFormation(Request $request)
    {
        $validated = $request->validate([
            'stage' => 'sometimes|string',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        $query = Jesuit::with([
                'user:id,name,email,phone_number', 
                'currentCommunity:id,name,code',
                'province:id,name,code',
                'region:id,name,code',
                'activeRoles' => function($query) {
                    $query->where('is_active', true)->whereNull('end_date');
                },
                'activeRoles.roleType:id,name',
                'activeRoles.assignable:id,name'
            ])
            ->where('province_id', $jesuit->province_id)
            ->where('is_active', true);

        if (isset($validated['stage'])) {
            $query->whereHas('formationStages', function($q) use ($validated) {
                $q->where('stage', $validated['stage'])
                  ->whereNull('end_date');
            });
        } else {
            $query->whereHas('formationStages', function($q) {
                $q->whereNull('end_date');
            });
        }

        $jesuits = $query->orderBy('joining_date', 'desc')
                         ->paginate($perPage);

        // Map results to match client interface format
        $jesuits->getCollection()->transform(function ($item) use ($jesuit) {
            return $this->transformJesuit($item, $jesuit->region_id);
        });

        return $this->successResponse($jesuits);
    }

    /**
     * Filter Jesuits in common houses.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inCommonHouses(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        $jesuits = Jesuit::with([
                'user:id,name,email,phone_number', 
                'currentCommunity:id,name,code',
                'province:id,name,code',
                'region:id,name,code',
                'activeRoles' => function($query) {
                    $query->where('is_active', true)->whereNull('end_date');
                },
                'activeRoles.roleType:id,name',
                'activeRoles.assignable:id,name'
            ])
            ->where('province_id', $jesuit->province_id)
            ->where('is_active', true)
            ->whereHas('currentCommunity', function($query) {
                $query->where('is_common_house', true);
            })
            ->orderBy('user_id')
            ->paginate($perPage);

        // Map results to match client interface format
        $jesuits->getCollection()->transform(function ($item) use ($jesuit) {
            return $this->transformJesuit($item, $jesuit->region_id);
        });

        return $this->successResponse($jesuits);
    }

    /**
     * Filter Jesuits working in other provinces.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inOtherProvinces(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        $jesuits = Jesuit::with([
                'user:id,name,email,phone_number', 
                'currentCommunity:id,name,code,province_id',
                'currentCommunity.province:id,name,code',
                'province:id,name,code',
                'region:id,name,code',
                'activeRoles' => function($query) {
                    $query->where('is_active', true)->whereNull('end_date');
                },
                'activeRoles.roleType:id,name',
                'activeRoles.assignable:id,name'
            ])
            ->where('province_id', $jesuit->province_id)
            ->where('is_active', true)
            ->whereHas('currentCommunity', function($query) use ($jesuit) {
                $query->whereHas('province', function($q) use ($jesuit) {
                    $q->where('id', '!=', $jesuit->province_id);
                });
            })
            ->orderBy('user_id')
            ->paginate($perPage);

        // Map results to match client interface format
        $jesuits->getCollection()->transform(function ($item) use ($jesuit) {
            return $this->transformJesuit($item, $jesuit->region_id);
        });

        return $this->successResponse($jesuits);
    }

    /**
     * Filter Jesuits working outside India.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function outsideIndia(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        $jesuits = Jesuit::with([
                'user:id,name,email,phone_number', 
                'currentCommunity:id,name,code,province_id,country',
                'currentCommunity.province:id,name,code',
                'province:id,name,code',
                'region:id,name,code',
                'activeRoles' => function($query) {
                    $query->where('is_active', true)->whereNull('end_date');
                },
                'activeRoles.roleType:id,name',
                'activeRoles.assignable:id,name'
            ])
            ->where('province_id', $jesuit->province_id)
            ->where('is_active', true)
            ->whereHas('currentCommunity', function($query) {
                $query->where('country', '!=', 'India');
            })
            ->orderBy('user_id')
            ->paginate($perPage);

        // Map results to match client interface format
        $jesuits->getCollection()->transform(function ($item) use ($jesuit) {
            return $this->transformJesuit($item, $jesuit->region_id);
        });

        return $this->successResponse($jesuits);
    }

    /**
     * Filter Jesuits from other provinces residing in our province.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function otherResiding(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        $jesuits = Jesuit::with([
                'user:id,name,email,phone_number',
                'currentCommunity:id,name,code',
                'province:id,name,code',
                'region:id,name,code',
                'activeRoles' => function($query) {
                    $query->where('is_active', true)->whereNull('end_date');
                },
                'activeRoles.roleType:id,name',
                'activeRoles.assignable:id,name'
            ])
            ->where('province_id', '!=', $jesuit->province_id)
            ->where('is_active', true)
            ->whereHas('currentCommunity', function($query) use ($jesuit) {
                $query->where('province_id', $jesuit->province_id);
            })
            ->orderBy('user_id')
            ->paginate($perPage);

        // Map results to match client interface format
        $jesuits->getCollection()->transform(function ($item) use ($jesuit) {
            return $this->transformJesuit($item, $jesuit->region_id);
        });

        return $this->successResponse($jesuits);
    }
} 