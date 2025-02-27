<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilamentCustomAuth extends \Filament\Http\Middleware\Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isAdmin()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
} 