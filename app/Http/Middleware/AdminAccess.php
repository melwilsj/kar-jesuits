<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (!in_array($request->user()->type, ['admin', 'superadmin'])) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        return $next($request);
    }
} 