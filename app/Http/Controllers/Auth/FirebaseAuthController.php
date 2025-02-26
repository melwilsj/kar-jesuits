<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FirebaseAuthController extends Controller
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuthService $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * Verify Firebase token and login user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'idToken' => 'required|string',
            'provider' => 'nullable|string|in:phone,google'
        ]);

        $provider = $validated['provider'] ?? 'phone';

        // Verify token
        $claims = $this->firebaseAuth->verifyIdToken($validated['idToken']);
        
        if (!$claims) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Firebase token'
            ], 401);
        }

        // Get Firebase user ID
        $firebaseUid = $claims['sub'];
        
        // Attempt to find user based on authentication method
        $user = null;
        
        if ($provider === 'google' && isset($claims['email'])) {
            // First try to find by email for Google auth
            $email = $claims['email'];
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Google account is not registered in our system',
                    'details' => 'The email associated with this Google account is not registered. Please contact an administrator.'
                ], 404);
            }
            
            // Update Firebase UID if needed
            if ($user->firebase_uid !== $firebaseUid) {
                $user->firebase_uid = $firebaseUid;
                $user->auth_provider = 'google';
                $user->save();
            }
        } elseif ($provider === 'phone' && isset($claims['phone_number'])) {
            // First try to find by phone number for phone auth
            $phoneNumber = $claims['phone_number'];
            $user = User::where('phone_number', $phoneNumber)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'This phone number is not registered in our system',
                    'details' => 'The phone number is not registered. Please contact an administrator.'
                ], 404);
            }
            
            // Update Firebase UID if needed
            if ($user->firebase_uid !== $firebaseUid) {
                $user->firebase_uid = $firebaseUid;
                $user->auth_provider = 'firebase';
                $user->save();
            }
        } else {
            // Try authenticating with Firebase UID as fallback
            $user = $this->firebaseAuth->authenticateWithUid($firebaseUid);
        }
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in our system',
                'details' => 'The credentials associated with this account are not registered in our system. Please contact an administrator.'
            ], 404);
        }

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive',
                'details' => 'Your account has been deactivated. Please contact an administrator.'
            ], 403);
        }

        // Log the user in
        Auth::login($user);

        // Determine redirect based on user type
        $redirect = '/dashboard';
        if (in_array($user->type, ['admin', 'superadmin'])) {
            $redirect = '/admin/dashboard';
        }

        // Generate API token for mobile app if requested
        $response = [
            'success' => true,
            'message' => 'Successfully authenticated',
            'redirect' => $redirect
        ];

        // Add token for API access if requested
        if ($request->has('api_access') && $request->api_access) {
            $response['token'] = $user->createToken('auth-token')->plainTextToken;
            $response['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'type' => $user->type
            ];
        }

        return response()->json($response);
    }

    /**
     * Verify phone number
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPhoneNumber(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'phone_number' => 'required|string'
        ]);

        $phone = $validated['phone_number'];
        
        // Check if user exists
        $user = User::where('phone_number', $phone)->first();
        
        return response()->json([
            'success' => true,
            'exists' => !!$user,
            'phone_number' => $phone
        ]);
    }

    /**
     * Web login page
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.firebase-login');
    }

    /**
     * Logout user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
} 