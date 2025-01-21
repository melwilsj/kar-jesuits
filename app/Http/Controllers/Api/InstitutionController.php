<?php

namespace App\Http\Controllers\Api;

use App\Models\Institution;
use Illuminate\Http\Request;

class InstitutionController extends BaseController
{
    public function index(Request $request)
    {
        $institutions = Institution::when(
            !$request->user()->hasRole('superadmin'),
            function ($query) use ($request) {
                if ($request->user()->hasRole('province_admin')) {
                    $query->whereHas('community', function ($q) use ($request) {
                        $q->whereIn('province_id', $request->user()->provinces->pluck('id'));
                    });
                } elseif ($request->user()->hasRole('region_admin')) {
                    $query->whereHas('community', function ($q) use ($request) {
                        $q->whereIn('region_id', $request->user()->regions->pluck('id'));
                    });
                } elseif ($request->user()->hasRole('community_superior')) {
                    $query->whereHas('community', function ($q) use ($request) {
                        $q->where('superior_id', $request->user()->id);
                    });
                }
            }
        )->with('community')->get();

        return $this->successResponse($institutions);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('manage_institutions')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'community_id' => 'required|exists:communities,id',
            'description' => 'nullable|string',
            'staff_count' => 'nullable|array'
        ]);

        $institution = Institution::create($validated);

        return $this->successResponse($institution, 'Institution created successfully', 201);
    }

    public function show(Request $request, Institution $institution)
    {
        if (!$request->user()->canAccessInstitution($institution)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($institution->load('community'));
    }

    public function update(Request $request, Institution $institution)
    {
        if (!$request->user()->canManageInstitution($institution)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'community_id' => 'sometimes|exists:communities,id',
            'description' => 'nullable|string',
            'staff_count' => 'nullable|array'
        ]);

        $institution->update($validated);

        return $this->successResponse($institution, 'Institution updated successfully');
    }

    public function destroy(Request $request, Institution $institution)
    {
        if (!$request->user()->canManageInstitution($institution)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $institution->delete();

        return $this->successResponse(null, 'Institution deleted successfully');
    }
} 