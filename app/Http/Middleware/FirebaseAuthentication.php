<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\FirebaseAuthService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuthentication
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuthService $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is not authenticated via Sanctum, return 401
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Get Firebase UID from the authenticated user
        $firebaseUid = $request->user()->firebase_uid;

        // If user doesn't have a Firebase UID, they're not properly authenticated
        if (!$firebaseUid) {
            Log::warning('User ID ' . $request->user()->id . ' attempted API access without Firebase authentication');
            return response()->json(['message' => 'Firebase authentication required'], 403);
        }

        // Verify Firebase token if present in the request
        $authHeader = $request->header('X-Firebase-Token');
        if ($authHeader) {
            $claims = $this->firebaseAuth->verifyIdToken($authHeader);
            
            if (!$claims || $claims['sub'] !== $firebaseUid) {
                Log::warning('Invalid Firebase token for user ID ' . $request->user()->id);
                return response()->json(['message' => 'Invalid Firebase token'], 401);
            }
        }

        return $next($request);
    }
}
