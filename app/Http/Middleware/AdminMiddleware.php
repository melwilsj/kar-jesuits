<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isAdmin()) {
            Auth::logout();
            return redirect()->route('welcome')
                ->with('error', 'You must be an administrator to access this area.');
        }

        return $next($request);
    }
} 