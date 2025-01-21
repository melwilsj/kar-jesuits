<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasProvinceAccess
{
    public function handle(Request $request, Closure $next)
    {
        $province = $request->route('province');
        
        if (!$request->user()->hasRole('superadmin') && 
            !$request->user()->provinces->contains($province->id)) {
            abort(403, 'Unauthorized access to province.');
        }

        return $next($request);
    }
} 