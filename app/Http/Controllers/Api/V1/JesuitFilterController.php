<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Jesuit;
use Illuminate\Http\Request;

class JesuitFilterController extends BaseController
{
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

        $query = Jesuit::with(['user:id,name,email,phone_number', 'currentCommunity:id,name,code'])
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

        $jesuits = Jesuit::with(['user:id,name,email,phone_number', 'currentCommunity:id,name,code'])
            ->where('province_id', $jesuit->province_id)
            ->where('is_active', true)
            ->whereHas('currentCommunity', function($query) {
                $query->where('is_common_house', true);
            })
            ->orderBy('user_id')
            ->paginate($perPage);

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
                'currentCommunity.province:id,name,code'
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
                'currentCommunity.province:id,name,code'
            ])
            ->where('province_id', $jesuit->province_id)
            ->where('is_active', true)
            ->whereHas('currentCommunity', function($query) {
                $query->where('country', '!=', 'India');
            })
            ->orderBy('user_id')
            ->paginate($perPage);

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
                'province:id,name,code'
            ])
            ->where('province_id', '!=', $jesuit->province_id)
            ->where('is_active', true)
            ->whereHas('currentCommunity', function($query) use ($jesuit) {
                $query->where('province_id', $jesuit->province_id);
            })
            ->orderBy('user_id')
            ->paginate($perPage);

        return $this->successResponse($jesuits);
    }
} 