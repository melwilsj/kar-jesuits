<?php

namespace App\Http\Controllers\Api;

use App\Models\{User, RoleAssignment, RoleType, Community};
use Illuminate\Http\Request;

class RoleAssignmentController extends BaseController
{
    public function assign(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_type_id' => 'required|exists:role_types,id',
            'assignable_type' => 'required|in:community,institution,province',
            'assignable_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string'
        ]);

        $this->authorize('create', [
            RoleAssignment::class, 
            $validated['assignable_type'], 
            $validated['assignable_id']
        ]);

        // End current active roles
        $user->roleAssignments()
            ->where('role_type_id', $validated['role_type_id'])
            ->where('assignable_type', $validated['assignable_type'])
            ->where('assignable_id', $validated['assignable_id'])
            ->where('is_active', true)
            ->update(['is_active' => false, 'end_date' => now()]);

        $assignment = $user->roleAssignments()->create($validated + ['is_active' => true]);

        return $this->successResponse(
            $assignment->load(['roleType', 'assignable']),
            'Role assigned successfully'
        );
    }

    public function history(Request $request, User $user)
    {
        if (!$request->user()->canViewMemberDetails($user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($user->roleAssignments()
            ->with(['roleType', 'assignable'])
            ->orderBy('start_date', 'desc')
            ->get());
    }

    public function endAssignment(Request $request, RoleAssignment $assignment)
    {
        if (!$request->user()->hasRole(['superadmin', 'province_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'end_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $assignment->update([
            'end_date' => $validated['end_date'],
            'notes' => $validated['notes'] ?? $assignment->notes,
            'is_active' => false
        ]);

        return $this->successResponse($assignment, 'Role assignment ended successfully');
    }
} 