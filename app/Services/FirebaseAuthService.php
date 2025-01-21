<?php

namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseAuthService
{
    private $auth;

    public function __construct()
    {
        $this->auth = Firebase::auth();
    }

    public function verifyIdToken(string $idToken): array
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            $user = $this->auth->getUser($verifiedIdToken->claims()->get('sub'));
            
            return [
                'uid' => $user->uid,
                'email' => $user->email,
                'phone_number' => $user->phoneNumber,
                'name' => $user->displayName,
                'picture' => $user->photoUrl
            ];
        } catch (\Exception $e) {
            \Log::error('Firebase token verification failed: ' . $e->getMessage());
            return [];
        }
    }

    public function verifyPhoneNumber(string $phoneNumber, string $code): bool
    {
        try {
            $this->auth->signInWithPhoneNumber($phoneNumber, $code);
            return true;
        } catch (\Exception $e) {
            \Log::error('Phone verification failed: ' . $e->getMessage());
            return false;
        }
    }
} 