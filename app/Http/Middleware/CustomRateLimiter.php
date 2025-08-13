<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response;

class CustomRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiter = 'api'): Response
    {
        $key = $this->resolveRequestSignature($request, $limiter);
        $maxAttempts = $this->getMaxAttempts($request, $limiter);
        $decayMinutes = $this->getDecayMinutes($limiter);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau.',
                'error' => [
                    'code' => 429,
                    'type' => 'RATE_LIMIT_EXCEEDED',
                    'retry_after' => $retryAfter,
                    'max_attempts' => $maxAttempts,
                    'decay_minutes' => $decayMinutes
                ],
                'timestamp' => now()->toISOString(),
                'status_code' => 429
            ], 429, [
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => max(0, $maxAttempts - RateLimiter::attempts($key)),
                'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->timestamp,
                'Retry-After' => $retryAfter,
            ]);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers to successful responses
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $maxAttempts - RateLimiter::attempts($key)),
            'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->timestamp,
        ]);

        return $response;
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request, string $limiter): string
    {
        $user = $request->user();
        
        // Different keys based on authentication status and user role
        if ($user) {
            $role = $user->roles->first()?->name ?? 'user';
            return "rate_limit:{$limiter}:user:{$user->id}:role:{$role}";
        }

        // For guests, use IP address
        return "rate_limit:{$limiter}:ip:" . $request->ip();
    }

    /**
     * Get max attempts based on limiter type and user role
     */
    protected function getMaxAttempts(Request $request, string $limiter): int
    {
        $user = $request->user();
        $role = $user?->roles->first()?->name ?? 'guest';

        return match($limiter) {
            'auth' => match($role) {
                'super_admin', 'admin' => 10,
                'editor', 'author' => 8,
                default => 5
            },
            'api' => match($role) {
                'super_admin' => 1000,
                'admin' => 500,
                'editor' => 300,
                'author' => 200,
                'user' => 100,
                default => 60
            },
            'upload' => match($role) {
                'super_admin', 'admin' => 50,
                'editor', 'author' => 30,
                'user' => 10,
                default => 5
            },
            'search' => match($role) {
                'super_admin', 'admin' => 200,
                'editor', 'author' => 150,
                'user' => 100,
                default => 50
            },
            default => 60
        };
    }

    /**
     * Get decay minutes based on limiter type
     */
    protected function getDecayMinutes(string $limiter): int
    {
        return match($limiter) {
            'auth' => 15,      // 15 minutes for auth attempts
            'upload' => 60,    // 1 hour for uploads
            'search' => 1,     // 1 minute for search
            default => 1       // 1 minute for general API
        };
    }
}
