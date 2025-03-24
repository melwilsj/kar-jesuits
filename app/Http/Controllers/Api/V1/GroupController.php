<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends BaseController
{
    public function index(Request $request)
    {
        $groups = Group::when(
            !$request->user()->hasRole('superadmin'),
            function ($query) use ($request) {
                if ($request->user()->hasRole('province_admin')) {
                    $query->whereIn('province_id', $request->user()->provinces->pluck('id'));
                } else {
                    $query->whereHas('members', function ($q) use ($request) {
                        $q->where('user_id', $request->user()->id);
                    });
                }
            }
        )->with(['members', 'province'])->get();

        return $this->successResponse($groups);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('manage_groups')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'province_id' => 'required|exists:provinces,id',
        ]);

        $group = Group::create($validated);

        return $this->successResponse($group, 'Group created successfully', 201);
    }

    public function show(Request $request, Group $group)
    {
        if (!$request->user()->canAccessGroup($group)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($group->load(['members', 'province']));
    }

    public function update(Request $request, Group $group)
    {
        if (!$request->user()->canManageGroup($group)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string',
            'province_id' => 'sometimes|exists:provinces,id',
        ]);

        $group->update($validated);

        return $this->successResponse($group, 'Group updated successfully');
    }

    public function destroy(Request $request, Group $group)
    {
        if (!$request->user()->canManageGroup($group)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $group->delete();

        return $this->successResponse(null, 'Group deleted successfully');
    }
} 