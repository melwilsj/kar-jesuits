<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\JesuitResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    public function show(User $user, Request $request)
    {
        if (!$request->user()->canViewMemberDetails($user)) {
            return $this->errorResponse('Unauthorized access', [], 403);
        }

        $user->load([
            'jesuit',
            'jesuit.currentCommunity',
            'jesuit.activeRoles.roleType'
        ]);

        return $this->successResponse(new UserResource($user));
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

    public function profile(Request $request)
    {
        $user = $request->user()->load([
            'jesuit.currentCommunity',
            'jesuit.activeRoles.roleType',
            'jesuit.formationStages',
            'jesuit.province',
            'jesuit.documents' => function($q) use ($user) {
                $q->where(function($query) use ($user) {
                    $query->where('visibility', 'public')
                          ->orWhere(function($q) use ($user) {
                              $q->where('visibility', 'private')
                                ->where('user_id', $user->id);
                          });
                });
            }
        ]);
        
        if (!$user->jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        return $this->successResponse(new JesuitResource($user->jesuit));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|string|unique:users,phone_number,' . $user->id,
            'current_password' => 'required_with:new_password|current_password',
            'new_password' => 'sometimes|min:8|confirmed',
        ]);

        if (isset($validated['new_password'])) {
            $validated['password'] = Hash::make($validated['new_password']);
            unset($validated['new_password']);
            unset($validated['current_password']);
        }

        $user->update($validated);

        return $this->successResponse(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Login a user and return an API token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        
        // Revoke all of the user's tokens for this device if they exist
        $user->tokens()->where('name', $request->device_name)->delete();
        
        // Create a new token
        $token = $user->createToken($request->device_name)->plainTextToken;
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ],
        ]);
    }
    
    /**
     * Register an FCM token for the authenticated user
     */
    public function registerFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);
        
        $user = $request->user();
        $user->addFcmToken($request->fcm_token);
        
        return response()->json([
            'success' => true,
            'message' => 'FCM token registered successfully',
        ]);
    }
    
    /**
     * Unregister an FCM token for the authenticated user
     */
    public function unregisterFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);
        
        $user = $request->user();
        $user->removeFcmToken($request->fcm_token);
        
        return response()->json([
            'success' => true,
            'message' => 'FCM token unregistered successfully',
        ]);
    }
} 