<?php

namespace App\Http\Controllers\Api;

use App\Models\{User, FormationStage};
use Illuminate\Http\Request;

class FormationController extends BaseController
{
    public function updateStage(Request $request, User $user)
    {
        if (!$request->user()->hasRole(['superadmin', 'province_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'nullable|string',
            'remarks' => 'nullable|string'
        ]);

        // Create new history entry
        $history = $user->jesuit->histories()->create([
            'community_id' => $user->jesuit->current_community_id,
            'province_id' => $user->jesuit->province_id,
            'category' => $user->jesuit->category,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'remarks' => $validated['remarks']
        ]);

        return $this->successResponse($history, 'History updated successfully');
    }

    public function history(Request $request, User $user)
    {
        if (!$request->user()->canViewMemberDetails($user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($user->jesuit->histories()
            ->with(['community', 'province'])
            ->orderBy('start_date', 'desc')
            ->get());
    }
} 