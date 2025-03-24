<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\{User, ExternalAssignment, Community};
use Illuminate\Http\Request;

class ExternalAssignmentController extends BaseController
{
    public function assign(Request $request, User $user)
    {
        $community = Community::findOrFail($request->input('assignable_id'));
        
        $this->authorize('create', [ExternalAssignment::class, $community]);

        $validated = $request->validate([
            'assignable_id' => 'required|exists:communities,id',
            'assignment_type' => 'required|in:studies,work,sabbatical',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'description' => 'nullable|string'
        ]);

        $validated['assignable_type'] = Community::class;
        $validated['is_active'] = true;

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