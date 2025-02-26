<?php

namespace App\Http\Controllers\Api\Auth;

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
     * Login with phone number via Firebase
     */
    public function phoneLogin(Request $request)
    {
        $request->validate([
            'idToken' => 'required|string',
        ]);

        try {
            // Verify the token from Firebase
            $verifiedIdToken = $this->firebaseAuthService->verifyIdToken($request->idToken);
            
            if (!$verifiedIdToken) {
                return response()->json(['message' => 'Invalid token'], 401);
            }

            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            
            // Get the phone number from the verified token
            $phoneNumber = $verifiedIdToken->claims()->get('phone_number');
            
            if (!$phoneNumber) {
                return response()->json(['message' => 'Phone number not found in token'], 400);
            }

            // Find the user by firebase_uid or phone number
            $user = User::where('firebase_uid', $firebaseUid)
                        ->orWhere('phone_number', $phoneNumber)
                        ->first();

            if (!$user) {
                return response()->json(['message' => 'User not found. Please contact administrator.'], 404);
            }
            
            if (!$user->is_active) {
                return response()->json(['message' => 'Your account is inactive. Please contact administrator.'], 403);
            }

            // Update user's Firebase UID if needed
            if ($user->firebase_uid !== $firebaseUid) {
                $user->firebase_uid = $firebaseUid;
                $user->auth_provider = 'firebase';
                $user->save();
            }

            // Create token for the React Native app
            $token = $user->createToken('react-native-app')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            Log::error('Phone login error: ' . $e->getMessage());
            return response()->json(['message' => 'Authentication failed: ' . $e->getMessage()], 401);
        }
    }

    /**
     * Login with Google via Firebase
     */
    public function googleLogin(Request $request)
    {
        $request->validate([
            'idToken' => 'required|string',
        ]);

        try {
            // Verify the token from Firebase
            $verifiedIdToken = $this->firebaseAuthService->verifyIdToken($request->idToken);
            
            if (!$verifiedIdToken) {
                return response()->json(['message' => 'Invalid token'], 401);
            }

            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');
            
            if (!$email) {
                return response()->json(['message' => 'Email not found in token'], 400);
            }

            // Find the user by firebase_uid or email
            $user = User::where('firebase_uid', $firebaseUid)
                        ->orWhere('email', $email)
                        ->first();

            if (!$user) {
                return response()->json(['message' => 'User not found. Please contact administrator.'], 404);
            }
            
            if (!$user->is_active) {
                return response()->json(['message' => 'Your account is inactive. Please contact administrator.'], 403);
            }

            // Update user's Firebase UID if needed
            if ($user->firebase_uid !== $firebaseUid) {
                $user->firebase_uid = $firebaseUid;
                $user->auth_provider = 'google';
                $user->save();
            }

            // Create token for the React Native app
            $token = $user->createToken('react-native-app')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());
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