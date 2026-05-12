<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // If user is owner, they can access everything
        if ($user->role === 'owner') {
            return $next($request);
        }

        // Check if user role is in the allowed roles
        // Mapping from DB role to semantic role names if necessary
        // In our DB: owner, supervisor, kasir, gudang, operator
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        return abort(403, 'Unauthorized action.');
    }
}
