<?php

namespace App\Http\Controllers\Api;

use App\Models\Province;
use Illuminate\Http\Request;

class ProvinceController extends BaseController
{
    public function index(Request $request)
    {
        $provinces = Province::when(
            !$request->user()->hasRole('superadmin'),
            function ($query) use ($request) {
                if ($request->user()->hasRole('province_admin')) {
                    $query->whereIn('id', $request->user()->provinces->pluck('id'));
                } elseif ($request->user()->hasRole('region_admin')) {
                    $query->whereIn('id', $request->user()->regions->pluck('province_id'));
                } elseif ($request->user()->hasRole('community_superior')) {
                    $query->whereIn('id', $request->user()->managedCommunities->pluck('province_id'));
                }
            }
        )->with(['regions', 'communities'])->get();

        return $this->successResponse($provinces);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('manage_provinces')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:provinces',
            'description' => 'nullable|string',
        ]);

        $province = Province::create($validated);

        return $this->successResponse($province, 'Province created successfully', 201);
    }

    public function show(Request $request, Province $province)
    {
        if (!$request->user()->canAccessProvince($province)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($province->load(['regions', 'communities']));
    }

    public function update(Request $request, Province $province)
    {
        if (!$request->user()->canManageProvince($province)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:provinces,code,' . $province->id,
            'description' => 'nullable|string',
        ]);

        $province->update($validated);

        return $this->successResponse($province, 'Province updated successfully');
    }

    public function destroy(Request $request, Province $province)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $province->delete();

        return $this->successResponse(null, 'Province deleted successfully');
    }
} 