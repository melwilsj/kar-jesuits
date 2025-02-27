<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // If user is admin and trying to access admin routes, let them through
                if (str_starts_with($request->path(), 'admin') && Auth::user()->isAdmin()) {
                    return $next($request);
                }
                
                // If user is admin, redirect to admin dashboard
                if (Auth::user()->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }
                
                // Otherwise redirect to regular dashboard
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
} 