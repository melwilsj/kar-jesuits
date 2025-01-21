<?php

namespace App\Http\Controllers\Api;

use App\Models\Commission;
use Illuminate\Http\Request;

class CommissionController extends BaseController
{
    public function index(Request $request)
    {
        $commissions = Commission::when(
            !$request->user()->hasRole('superadmin'),
            function ($query) use ($request) {
                if ($request->user()->hasRole('province_admin')) {
                    $query->whereIn('province_id', $request->user()->provinces->pluck('id'));
                } elseif ($request->user()->hasRole('commission_head')) {
                    $query->whereHas('members', function ($q) use ($request) {
                        $q->where('user_id', $request->user()->id)
                          ->where('role', 'head');
                    });
                }
            }
        )->with(['members', 'province'])->get();

        return $this->successResponse($commissions);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('manage_commissions')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'description' => 'nullable|string',
        ]);

        $commission = Commission::create($validated);

        return $this->successResponse($commission, 'Commission created successfully', 201);
    }

    public function show(Request $request, Commission $commission)
    {
        if (!$request->user()->canAccessCommission($commission)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($commission->load(['members', 'province']));
    }

    public function update(Request $request, Commission $commission)
    {
        if (!$request->user()->canManageCommission($commission)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'province_id' => 'sometimes|exists:provinces,id',
            'description' => 'nullable|string',
        ]);

        $commission->update($validated);

        return $this->successResponse($commission, 'Commission updated successfully');
    }

    public function destroy(Request $request, Commission $commission)
    {
        if (!$request->user()->canManageCommission($commission)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $commission->delete();

        return $this->successResponse(null, 'Commission deleted successfully');
    }
} 