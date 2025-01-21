<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view_users')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $users = User::when(
            !$request->user()->hasRole('superadmin'),
            function ($query) use ($request) {
                if ($request->user()->hasRole('province_admin')) {
                    $query->whereHas('provinces', function ($q) use ($request) {
                        $q->whereIn('province_id', $request->user()->provinces->pluck('id'));
                    });
                } elseif ($request->user()->hasRole('region_admin')) {
                    $query->whereHas('regions', function ($q) use ($request) {
                        $q->whereIn('region_id', $request->user()->regions->pluck('id'));
                    });
                } elseif ($request->user()->hasRole('community_superior')) {
                    $query->whereHas('managedCommunities', function ($q) use ($request) {
                        $q->where('superior_id', $request->user()->id);
                    });
                }
            }
        )->with(['roles', 'provinces', 'regions', 'managedCommunities'])->get();

        return $this->successResponse($users);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('manage_users')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone_number' => 'nullable|string|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password'])
        ]);

        $user->roles()->attach($validated['roles']);

        return $this->successResponse($user->load('roles'), 'User created successfully', 201);
    }

    public function show(Request $request, User $user)
    {
        if (!$request->user()->hasPermission('view_users')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($user->load(['roles', 'provinces', 'regions', 'managedCommunities']));
    }

    public function update(Request $request, User $user)
    {
        if (!$request->user()->hasPermission('manage_users')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|unique:users,phone_number,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        return $this->successResponse($user->load('roles'), 'User updated successfully');
    }

    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }
} 