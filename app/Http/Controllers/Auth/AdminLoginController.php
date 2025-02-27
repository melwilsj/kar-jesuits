<?php
// TODO: DELETE THIS FILE
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminLoginController extends Controller
{
    protected $firebaseAuth;
    
    public function __construct(FirebaseAuthService $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
        $this->middleware('guest')->except('logout');
    }
    
    /**
     * Show admin login form
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.admin-login');
    }
    
    /**
     * Handle admin login attempt with email/password
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validate request
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        
        // Attempt to login
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();
            
            // Check if user is admin
            if (!in_array($user->type, ['admin', 'superadmin'])) {
                Auth::logout();
                return redirect()->back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => 'You do not have admin privileges.'
                    ]);
            }
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return redirect()->back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => 'Your account is inactive. Please contact the administrator.'
                    ]);
            }
            
            // Regenerate session
            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.dashboard'));
        }
        
        // Authentication failed
        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'These credentials do not match our records.'
            ]);
    }
    
    /**
     * Verify Firebase token for admin login
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyFirebaseToken(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'idToken' => 'required|string'
        ]);
        
        // Verify token
        $claims = $this->firebaseAuth->verifyIdToken($validated['idToken']);
        
        if (!$claims) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Firebase token'
            ], 401);
        }
        
        // Authenticate user
        $user = $this->firebaseAuth->authenticateWithUid($claims['sub']);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found in our system'
            ], 404);
        }
        
        // Check if user is admin
        if (!in_array($user->type, ['admin', 'superadmin'])) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'You do not have admin privileges'
            ], 403);
        }
        
        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'redirect' => route('admin.dashboard')
        ]);
    }
    
    /**
     * Logout admin
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
} 