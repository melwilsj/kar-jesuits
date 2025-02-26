<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->type !== 'superadmin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        return $next($request);
    }
} 