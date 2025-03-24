<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Community;
use Illuminate\Http\Request;

class CommunityController extends BaseController
{
    public function index(Request $request)
    {
        $communities = Community::when(
            !$request->user()->hasRole('superadmin'),
            function ($query) use ($request) {
                if ($request->user()->hasRole('province_admin')) {
                    $query->whereIn('province_id', $request->user()->provinces->pluck('id'));
                } elseif ($request->user()->hasRole('region_admin')) {
                    $query->whereIn('region_id', $request->user()->regions->pluck('id'));
                } elseif ($request->user()->hasRole('community_superior')) {
                    $query->where('superior_id', $request->user()->id);
                }
            }
        )->with(['institutions'])->get();

        return $this->successResponse($communities);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('manage_communities')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:communities',
            'province_id' => 'required|exists:provinces,id',
            'region_id' => 'nullable|exists:regions,id',
            'superior_id' => 'nullable|exists:users,id',
            'address' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email'
        ]);

        $community = Community::create($validated);

        return $this->successResponse($community, 'Community created successfully', 201);
    }

    public function show(Request $request, Community $community)
    {
        if (!$request->user()->canAccessCommunity($community)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($community->load(['institutions', 'province', 'region', 'superior']));
    }

    public function update(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:communities,code,' . $community->id,
            'province_id' => 'sometimes|exists:provinces,id',
            'region_id' => 'nullable|exists:regions,id',
            'superior_id' => 'nullable|exists:users,id',
            'address' => 'sometimes|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email'
        ]);

        $community->update($validated);

        return $this->successResponse($community, 'Community updated successfully');
    }

    public function destroy(Request $request, Community $community)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $community->delete();

        return $this->successResponse(null, 'Community deleted successfully');
    }
} 