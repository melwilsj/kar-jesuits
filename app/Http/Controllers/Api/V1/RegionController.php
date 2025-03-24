<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends BaseController
{
    public function index(Request $request)
    {
        $regions = Region::when(
            !$request->user()->hasRole('superadmin'),
            function ($query) use ($request) {
                if ($request->user()->hasRole('province_admin')) {
                    $query->whereIn('province_id', $request->user()->provinces->pluck('id'));
                } elseif ($request->user()->hasRole('region_admin')) {
                    $query->whereIn('id', $request->user()->regions->pluck('id'));
                } elseif ($request->user()->hasRole('community_superior')) {
                    $query->whereIn('id', $request->user()->managedCommunities->pluck('region_id'));
                }
            }
        )->with(['communities'])->get();

        return $this->successResponse($regions);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('manage_regions')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:regions',
            'description' => 'nullable|string',
            'province_id' => 'required|exists:provinces,id'
        ]);

        $region = Region::create($validated);

        return $this->successResponse($region, 'Region created successfully', 201);
    }

    public function show(Request $request, Region $region)
    {
        if (!$request->user()->canAccessRegion($region)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($region->load(['communities', 'province']));
    }

    public function update(Request $request, Region $region)
    {
        if (!$request->user()->canManageRegion($region)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:regions,code,' . $region->id,
            'description' => 'nullable|string',
            'province_id' => 'sometimes|exists:provinces,id'
        ]);

        $region->update($validated);

        return $this->successResponse($region, 'Region updated successfully');
    }

    public function destroy(Request $request, Region $region)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $region->delete();

        return $this->successResponse(null, 'Region deleted successfully');
    }
} 