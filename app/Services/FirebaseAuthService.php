<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseAuthService
{
    protected $auth;

    public function __construct()
    {
        $this->auth = Firebase::auth();
    }

    /**
     * Verify a Firebase ID token
     * 
     * @param string $idToken
     * @return array|null
     */
    public function verifyIdToken(string $idToken)
    {
        try {
            // Verify the ID token
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            
            // Get the claims from the token
            $claims = $verifiedIdToken->claims()->all();
            
            return $claims;
        } catch (\Exception $e) {
            Log::error('Firebase token verification failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Authenticate a user with Firebase UID
     * 
     * @param string $firebaseUid
     * @return User|null
     */
    public function authenticateWithUid(string $firebaseUid)
    {
        try {
            // Get Firebase user record
            $firebaseUser = $this->auth->getUser($firebaseUid);
            
            // Find matching user in our database
            $user = null;
            
            // Try to find by email if available
            if (!empty($firebaseUser->email)) {
                $user = User::where('email', $firebaseUser->email)->first();
            }
            
            // Try to find by phone if available and no user found by email
            if (!$user && !empty($firebaseUser->phoneNumber)) {
                $user = User::where('phone_number', $firebaseUser->phoneNumber)->first();
            }
            
            // Try to find by firebase_uid
            if (!$user) {
                $user = User::where('firebase_uid', $firebaseUid)->first();
            }
            
            if (!$user) {
                Log::warning("No matching user found for Firebase UID: {$firebaseUid}");
                return null;
            }
            
            // Update user's Firebase details
            $user->firebase_uid = $firebaseUid;
            $user->auth_provider = $this->determineAuthProvider($firebaseUser);
            
            // Update email or phone if we don't have it
            if (empty($user->email) && !empty($firebaseUser->email)) {
                $user->email = $firebaseUser->email;
            }
            
            if (empty($user->phone_number) && !empty($firebaseUser->phoneNumber)) {
                $user->phone_number = $firebaseUser->phoneNumber;
            }
            
            $user->save();
            
            return $user;
        } catch (UserNotFound $e) {
            Log::error("Firebase user not found: {$e->getMessage()}");
            return null;
        } catch (AuthException $e) {
            Log::error("Firebase auth error: {$e->getMessage()}");
            return null;
        } catch (\Exception $e) {
            Log::error("Error authenticating with Firebase: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Determine the auth provider from Firebase user data
     * 
     * @param \Kreait\Firebase\Auth\UserRecord $firebaseUser
     * @return string
     */
    protected function determineAuthProvider($firebaseUser)
    {
        if (!empty($firebaseUser->providerData)) {
            foreach ($firebaseUser->providerData as $provider) {
                if ($provider->providerId === 'google.com') {
                    return 'google';
                }
                if ($provider->providerId === 'phone') {
                    return 'firebase';
                }
            }
        }
        
        return 'firebase'; // Default provider
    }

    /**
     * Generate a custom token for a user
     * 
     * @param User $user
     * @return string|null
     */
    public function createCustomToken(User $user)
    {
        try {
            // Create or update Firebase user
            $firebaseUid = $user->firebase_uid;
            
            if (!$firebaseUid) {
                // Create a new Firebase user
                $properties = [
                    'email' => $user->email,
                    'phoneNumber' => $user->phone_number,
                    'displayName' => $user->name,
                ];
                
                $firebaseUser = $this->auth->createUser($properties);
                $firebaseUid = $firebaseUser->uid;
                
                // Update user
                $user->firebase_uid = $firebaseUid;
                $user->save();
            }
            
            // Generate custom token
            return $this->auth->createCustomToken($firebaseUid);
        } catch (\Exception $e) {
            Log::error("Error creating custom token: {$e->getMessage()}");
            return null;
        }
    }
} 