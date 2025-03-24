<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Commission;
use Illuminate\Http\Request;

class CommissionController extends BaseController
{
    /**
     * Get all commissions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
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

        $commissions = Commission::with([
                'province:id,name,code',
                'members.user:id,name,email,phone_number',
                'head.user:id,name,email,phone_number'
            ])
            ->where('province_id', $jesuit->province_id)
            ->orderBy('name')
            ->paginate($perPage);

        return $this->successResponse($commissions);
    }

    /**
     * Filter commissions by type.
     *
     * @param Request $request
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function byType(Request $request, $type)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        // Validate type parameter
        if (!in_array($type, ['education', 'social', 'formation', 'pastoral'])) {
            return $this->errorResponse('Invalid commission type. Must be one of: education, social, formation, pastoral');
        }

        $perPage = $validated['per_page'] ?? 15;
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        $commissions = Commission::with([
                'province:id,name,code',
                'members.user:id,name,email,phone_number',
                'head.user:id,name,email,phone_number'
            ])
            ->where('province_id', $jesuit->province_id)
            ->where('type', $type)
            ->orderBy('name')
            ->paginate($perPage);

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