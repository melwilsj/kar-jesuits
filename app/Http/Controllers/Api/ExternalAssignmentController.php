<?php

namespace App\Http\Controllers\Api;

use App\Models\{User, ExternalAssignment, CommonHouse};
use Illuminate\Http\Request;

class ExternalAssignmentController extends BaseController
{
    public function assign(Request $request, User $user)
    {
        if (!$request->user()->hasRole(['superadmin', 'province_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'assignable_type' => 'required|in:common_houses,communities',
            'assignable_id' => 'required|integer',
            'assignment_type' => 'required|in:studies,work,sabbatical',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'description' => 'nullable|string'
        ]);

        // End current active assignments
        $user->externalAssignments()
            ->where('is_active', true)
            ->update(['is_active' => false, 'end_date' => now()]);

        $assignment = $user->externalAssignments()->create($validated);

        return $this->successResponse(
            $assignment->load('assignable'), 
            'External assignment created successfully'
        );
    }

    public function history(Request $request, User $user)
    {
        if (!$request->user()->canViewMemberDetails($user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($user->externalAssignments()
            ->with('assignable')
            ->orderBy('start_date', 'desc')
            ->get());
    }
} 