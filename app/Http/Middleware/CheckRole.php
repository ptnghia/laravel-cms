<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Comma-separated list of roles
     * @param  string  $logic  'and' or 'or' logic (default: 'or')
     */
    public function handle(Request $request, Closure $next, string $roles, string $logic = 'or'): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Load roles if not already loaded
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        $requiredRoles = explode(',', $roles);
        $userRoles = $user->roles->pluck('name')->toArray();

        $hasRole = false;

        if ($logic === 'and') {
            // User must have ALL required roles
            $hasRole = empty(array_diff($requiredRoles, $userRoles));
        } else {
            // User must have AT LEAST ONE required role
            $hasRole = !empty(array_intersect($requiredRoles, $userRoles));
        }

        if (!$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required roles: ' . implode(', ', $requiredRoles),
            ], 403);
        }

        return $next($request);
    }
}
