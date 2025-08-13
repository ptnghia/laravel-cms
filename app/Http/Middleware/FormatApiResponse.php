<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormatApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only format JSON responses for API routes
        if ($response instanceof JsonResponse && $request->is('api/*')) {
            $data = $response->getData(true);

            // Add standard API response structure if not already present
            if (!isset($data['success'])) {
                $statusCode = $response->getStatusCode();

                $formattedData = [
                    'success' => $statusCode >= 200 && $statusCode < 300,
                    'message' => $this->getDefaultMessage($statusCode),
                    'timestamp' => now()->toISOString(),
                    'status_code' => $statusCode,
                ];

                // Add data if present
                if (!empty($data)) {
                    $formattedData['data'] = $data;
                }

                // Add error details for error responses
                if ($statusCode >= 400) {
                    $formattedData['error'] = [
                        'code' => $statusCode,
                        'type' => $this->getErrorType($statusCode),
                    ];
                }

                $response->setData($formattedData);
            } else {
                // Add missing fields to existing structure
                if (!isset($data['timestamp'])) {
                    $data['timestamp'] = now()->toISOString();
                }

                if (!isset($data['status_code'])) {
                    $data['status_code'] = $response->getStatusCode();
                }

                $response->setData($data);
            }

            // Add common headers
            $response->headers->set('X-API-Version', config('app.api_version', '1.0'));
            $response->headers->set('X-Request-ID', $request->header('X-Request-ID', uniqid()));
            $response->headers->set('X-Response-Time', round((microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000, 2) . 'ms');
        }

        return $response;
    }

    /**
     * Get default message for status code.
     */
    private function getDefaultMessage(int $statusCode): string
    {
        return match ($statusCode) {
            200 => 'Success',
            201 => 'Created successfully',
            204 => 'No content',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            422 => 'Validation failed',
            429 => 'Too many requests',
            500 => 'Internal server error',
            default => 'Unknown status',
        };
    }

    /**
     * Get error type for status code.
     */
    private function getErrorType(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            422 => 'VALIDATION_ERROR',
            429 => 'RATE_LIMIT_EXCEEDED',
            500 => 'INTERNAL_ERROR',
            default => 'UNKNOWN_ERROR',
        };
    }
}
