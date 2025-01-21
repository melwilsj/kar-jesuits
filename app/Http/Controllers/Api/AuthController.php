<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    public function __construct(private FirebaseAuthService $firebaseAuth)
    {}

    public function login(Request $request)
    {
        $token = $request->header('Firebase-Token');
        
        if (!$token) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $firebaseUser = $this->firebaseAuth->verifyIdToken($token);
        
        if (empty($firebaseUser)) {
            return $this->errorResponse('Invalid token', 401);
        }

        // Find or create user
        $user = User::where('email', $firebaseUser['email'])
            ->orWhere('phone_number', $firebaseUser['phone_number'])
            ->first();

        if (!$user) {
            // Create new user if not exists
            $user = User::create([
                'name' => $firebaseUser['name'] ?? 'New User',
                'email' => $firebaseUser['email'],
                'phone_number' => $firebaseUser['phone_number'],
                'password' => Hash::make(Str::random(16)),
                'profile_photo' => $firebaseUser['picture'] ?? null
            ]);

            // Assign default role
            $user->roles()->attach(Role::where('slug', 'jesuit')->first());
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => $user->load('roles.permissions'),
        ]);
    }

    public function verifyPhone(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'verification_code' => 'required|string'
        ]);

        if (!$this->firebaseAuth->verifyPhoneNumber($validated['phone_number'], $validated['verification_code'])) {
            return $this->errorResponse('Invalid verification code', 400);
        }

        $user = User::where('phone_number', $validated['phone_number'])->first();

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => $user->load('roles.permissions'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logged out successfully');
    }
} 