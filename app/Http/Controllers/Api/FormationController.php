<?php

namespace App\Http\Controllers\Api;

use App\Models\{User, JesuitFormation, FormationStage};
use Illuminate\Http\Request;

class FormationController extends BaseController
{
    public function updateStage(Request $request, User $user)
    {
        if (!$request->user()->hasRole(['superadmin', 'province_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'stage_id' => 'required|exists:formation_stages,id',
            'current_year' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        // End current formation stage if exists
        $user->currentFormation?->update(['end_date' => now()]);

        // Create new formation stage
        $formation = $user->formationHistory()->create($validated);

        return $this->successResponse($formation->load('stage'), 'Formation stage updated successfully');
    }

    public function formationHistory(Request $request, User $user)
    {
        if (!$request->user()->canViewMemberDetails($user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($user->formationHistory()
            ->with('stage')
            ->orderBy('start_date', 'desc')
            ->get());
    }
} 