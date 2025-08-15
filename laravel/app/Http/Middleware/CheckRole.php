<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        $userRole = strtolower(str_replace(' ', '', $user->role));
        $normalizedRoles = array_map(function ($role) {
            return strtolower(str_replace(' ', '', $role));
        }, $roles);
        
        if (in_array($userRole, $normalizedRoles)) {
            return $next($request);
        }

        return redirect()->route('access.denied');
    }
}