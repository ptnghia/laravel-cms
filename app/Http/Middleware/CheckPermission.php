<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permissions  Comma-separated list of permissions
     * @param  string  $logic  'and' or 'or' logic (default: 'or')
     */
    public function handle(Request $request, Closure $next, string $permissions, string $logic = 'or'): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $requiredPermissions = explode(',', $permissions);

        // Get user permissions through roles
        $userPermissions = $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->toArray();

        $hasPermission = false;

        if ($logic === 'and') {
            // User must have ALL required permissions
            $hasPermission = empty(array_diff($requiredPermissions, $userPermissions));
        } else {
            // User must have AT LEAST ONE required permission
            $hasPermission = !empty(array_intersect($requiredPermissions, $userPermissions));
        }

        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required permissions: ' . implode(', ', $requiredPermissions),
            ], 403);
        }

        return $next($request);
    }
}
