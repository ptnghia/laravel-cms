<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogActivity
{
    /**
     * Routes that should not be logged
     */
    protected array $excludedRoutes = [
        'api/health',
        'api/ping',
        'api/status',
    ];

    /**
     * HTTP methods that should not be logged
     */
    protected array $excludedMethods = [
        'OPTIONS',
    ];

    /**
     * Sensitive fields that should be masked in logs
     */
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'api_key',
        'secret',
        'credit_card',
        'ssn',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Skip logging for excluded routes and methods
        if ($this->shouldSkipLogging($request)) {
            return $next($request);
        }

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // milliseconds

        // Log the activity asynchronously to avoid performance impact
        $this->logActivity($request, $response, $duration);

        return $response;
    }

    /**
     * Check if logging should be skipped
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        // Skip excluded HTTP methods
        if (in_array($request->getMethod(), $this->excludedMethods)) {
            return true;
        }

        // Skip excluded routes
        $path = $request->path();
        foreach ($this->excludedRoutes as $excludedRoute) {
            if (str_starts_with($path, $excludedRoute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the activity
     */
    protected function logActivity(Request $request, Response $response, float $duration): void
    {
        try {
            $user = Auth::user();
            $statusCode = $response->getStatusCode();

            // Prepare activity data
            $activityData = [
                'user_id' => $user?->id,
                'action' => $this->getActionName($request),
                'description' => $this->getDescription($request, $statusCode),
                'url' => $request->fullUrl(),
                'method' => $request->getMethod(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => $this->sanitizePayload($request->all()),
                'response_code' => $statusCode,
                'duration_ms' => $duration,
                'api_version' => $request->attributes->get('api_version', 'v1'),
                'metadata' => [
                    'route_name' => $request->route()?->getName(),
                    'controller' => $this->getControllerName($request),
                    'middleware' => $request->route()?->middleware() ?? [],
                    'headers' => $this->getImportantHeaders($request),
                    'query_count' => $this->getQueryCount(),
                    'memory_usage' => $this->getMemoryUsage(),
                ],
            ];

            // Create activity log entry
            ActivityLog::create($activityData);

            // Log to Laravel log for debugging if needed
            if (config('app.debug') && $statusCode >= 400) {
                Log::warning('API Error Activity', [
                    'user_id' => $user?->id,
                    'url' => $request->fullUrl(),
                    'method' => $request->getMethod(),
                    'status_code' => $statusCode,
                    'duration_ms' => $duration,
                ]);
            }

        } catch (Throwable $e) {
            // Don't let logging errors break the application
            Log::error('Failed to log activity', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->getMethod(),
            ]);
        }
    }

    /**
     * Get action name from request
     */
    protected function getActionName(Request $request): string
    {
        $route = $request->route();
        
        if ($route && $route->getName()) {
            return $route->getName();
        }

        // Fallback to method + path
        $method = strtolower($request->getMethod());
        $path = $request->path();
        
        return "{$method}:{$path}";
    }

    /**
     * Get description for the activity
     */
    protected function getDescription(Request $request, int $statusCode): string
    {
        $method = $request->getMethod();
        $path = $request->path();
        $user = Auth::user();
        
        $userInfo = $user ? "User {$user->name} (ID: {$user->id})" : 'Guest';
        $statusText = $statusCode >= 400 ? 'failed' : 'accessed';
        
        return "{$userInfo} {$statusText} {$method} {$path} (HTTP {$statusCode})";
    }

    /**
     * Get controller name from request
     */
    protected function getControllerName(Request $request): ?string
    {
        $route = $request->route();
        
        if ($route && $route->getAction('controller')) {
            return $route->getAction('controller');
        }

        return null;
    }

    /**
     * Sanitize payload by masking sensitive fields
     */
    protected function sanitizePayload(array $payload): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = '***MASKED***';
            }
        }

        return $payload;
    }

    /**
     * Get important headers for logging
     */
    protected function getImportantHeaders(Request $request): array
    {
        $importantHeaders = [
            'Accept',
            'Content-Type',
            'Authorization',
            'X-API-Version',
            'X-Requested-With',
            'Referer',
        ];

        $headers = [];
        foreach ($importantHeaders as $header) {
            if ($request->hasHeader($header)) {
                $value = $request->header($header);
                // Mask authorization header
                if ($header === 'Authorization' && $value) {
                    $headers[$header] = 'Bearer ***MASKED***';
                } else {
                    $headers[$header] = $value;
                }
            }
        }

        return $headers;
    }

    /**
     * Get database query count (if query log is enabled)
     */
    protected function getQueryCount(): int
    {
        try {
            return count(\DB::getQueryLog());
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Get memory usage in MB
     */
    protected function getMemoryUsage(): float
    {
        return round(memory_get_peak_usage(true) / 1024 / 1024, 2);
    }
}
