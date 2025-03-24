<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\{User, JesuitProfile, JesuitFormation};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JesuitProfileController extends BaseController
{
    public function show(Request $request, User $user)
    {
        if (!$request->user()->canViewMemberDetails($user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($user->load([
            'profile',
            'currentFormation.stage',
            'formationStages',
            'activeRoles.roleType',
            'currentCommunity',
            'documents' => function ($query) use ($request) {
                if (!$request->user()->hasRole('superadmin')) {
                    $query->where(function($q) use ($request) {
                        $q->where('visibility', 'public')
                          ->orWhere(function($q) use ($request) {
                              $q->where('visibility', 'private')
                                ->where('user_id', $request->user()->id);
                          });
                    });
                }
            }
        ]));
    }

    public function update(Request $request, User $user)
    {
        if (!$request->user()->canManageMemberDetails($user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'dob' => 'required|date',
            'joining_date' => 'required|date',
            'priesthood_date' => 'nullable|date',
            'final_vows_date' => 'nullable|date',
            'academic_qualifications' => 'nullable|array',
            'publications' => 'nullable|array',
            'profile_photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::delete($user->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('profile-photos', 'public');
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return $this->successResponse($user->load('profile'), 'Profile updated successfully');
    }
} 