<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class FirebaseAuthController extends Controller
{
    protected $firebaseAuthService;

    public function __construct(FirebaseAuthService $firebaseAuthService)
    {
        $this->firebaseAuthService = $firebaseAuthService;
    }

    /**
     * Verify a Firebase token and return the user
     */
    public function verifyToken(Request $request)
    {
        $request->validate([
            'idToken' => 'required|string',
        ]);

        try {
            $verifiedIdToken = $this->firebaseAuthService->verifyIdToken($request->idToken);
            
            if (!$verifiedIdToken) {
                return response()->json(['message' => 'Invalid token'], 401);
            }

            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user || !$user->is_active) {
                return response()->json(['message' => 'User not found or inactive'], 404);
            }

            // Create token for the React Native app
            $token = $user->createToken('react-native-app')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            Log::error('Firebase token verification error: ' . $e->getMessage());
            return response()->json(['message' => 'Authentication failed'], 401);
        }
    }

    /**
     * Check if phone number exists
     */
    public function verifyPhoneNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string'
        ]);

        $exists = User::where('phone_number', $request->phone_number)
                     ->where('is_active', true)
                     ->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    /**
     * Login with phone number via Firebase
     */
    public function phoneLogin(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'verification_code' => 'required|string',
            'id_token' => 'required|string',
        ]);

        try {
            $verifiedIdToken = $this->firebaseAuthService->verifyIdToken($request->id_token);
            
            if (!$verifiedIdToken) {
                return response()->json(['message' => 'Invalid token'], 401);
            }

            $firebaseUid = $verifiedIdToken['sub'] ?? null;
            $phoneNumber = $verifiedIdToken['phone_number'] ?? null;
            
            if (!$phoneNumber) {
                return response()->json(['message' => 'Phone number not found in token'], 400);
            }

            $user = User::where('phone_number', $request->phone_number)
                       ->where('is_active', true)
                       ->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Update Firebase UID if needed
            if ($user->firebase_uid !== $firebaseUid) {
                $user->firebase_uid = $firebaseUid;
                $user->auth_provider = 'firebase';
                $user->save();
            }

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user->only(['id', 'name', 'email', 'phone_number', 'type', 'is_active'])
            ]);

        } catch (\Exception $e) {
            Log::error('Phone login error: ' . $e->getMessage());
            return response()->json(['message' => 'Authentication failed'], 401);
        }
    }

    /**
     * Login with Google via Firebase
     */
    public function googleLogin(Request $request)
    {
        try {
            $request->validate([
                'id_token' => 'required|string'
            ]);

            Log::info('Google login attempt', [
                'token_length' => strlen($request->id_token)
            ]);

            $verifiedIdToken = $this->firebaseAuthService->verifyIdToken($request->id_token);
            
            if (!$verifiedIdToken) {
                Log::error('Invalid token verification');
                return response()->json(['message' => 'Invalid token'], 401);
            }

            $firebaseUid = $verifiedIdToken['sub'] ?? null;
            $email = $verifiedIdToken['email'] ?? null;

            if (!$email) {
                return response()->json(['message' => 'Email not found in token'], 400);
            }

            $user = User::where('email', $email)
                       ->where('is_active', true)
                       ->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Update Firebase UID if needed
            if ($user->firebase_uid !== $firebaseUid) {
                $user->firebase_uid = $firebaseUid;
                $user->auth_provider = 'google';
                $user->save();
            }

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user->only(['id', 'name', 'email', 'phone_number', 'type', 'is_active'])
            ]);

        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Authentication failed: ' . $e->getMessage()], 401);
        }
    }

    /**
     * Logout the user from the API
     */
    public function logout(Request $request)
    {
        try {
            // Revoke the token that was used to authenticate the current request
            $request->user()->currentAccessToken()->delete();
            
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json(['message' => 'Logout failed'], 500);
        }
    }
} 