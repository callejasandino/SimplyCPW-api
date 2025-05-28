<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();
        return $next($request);
        // if (!$user) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        // if (in_array($user->role, $roles)) {
        //     return $next($request);
        // }

        return response()->json(['message' => 'Forbidden - Insufficient Permissions'], 403);
    }
}
