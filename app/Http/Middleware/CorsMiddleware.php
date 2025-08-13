<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            return $this->handlePreflightRequest($request);
        }

        $response = $next($request);

        return $this->addCorsHeaders($request, $response);
    }

    /**
     * Handle preflight OPTIONS request
     */
    protected function handlePreflightRequest(Request $request): Response
    {
        $response = response('', 200);
        
        return $this->addCorsHeaders($request, $response);
    }

    /**
     * Add CORS headers to response
     */
    protected function addCorsHeaders(Request $request, Response $response): Response
    {
        $origin = $request->header('Origin');
        $allowedOrigins = $this->getAllowedOrigins();

        // Check if origin is allowed
        if ($this->isOriginAllowed($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        $response->headers->set('Access-Control-Allow-Methods', $this->getAllowedMethods());
        $response->headers->set('Access-Control-Allow-Headers', $this->getAllowedHeaders());
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
        $response->headers->set('Access-Control-Expose-Headers', $this->getExposedHeaders());

        return $response;
    }

    /**
     * Get allowed origins based on environment
     */
    protected function getAllowedOrigins(): array
    {
        $origins = config('cors.allowed_origins', []);

        // Default origins based on environment
        if (empty($origins)) {
            if (app()->environment('local', 'development')) {
                return [
                    'http://localhost:3000',
                    'http://localhost:8080',
                    'http://localhost:5173',
                    'http://127.0.0.1:3000',
                    'http://127.0.0.1:8080',
                    'http://127.0.0.1:5173',
                    'http://localhost:8000',
                    'http://127.0.0.1:8000',
                ];
            }

            if (app()->environment('staging')) {
                return [
                    'https://staging.laravel-cms.com',
                    'https://admin-staging.laravel-cms.com',
                ];
            }

            if (app()->environment('production')) {
                return [
                    'https://laravel-cms.com',
                    'https://www.laravel-cms.com',
                    'https://admin.laravel-cms.com',
                ];
            }
        }

        return $origins;
    }

    /**
     * Check if origin is allowed
     */
    protected function isOriginAllowed(?string $origin, array $allowedOrigins): bool
    {
        if (!$origin) {
            return false;
        }

        // Allow all origins in development
        if (app()->environment('local', 'development') && in_array('*', $allowedOrigins)) {
            return true;
        }

        return in_array($origin, $allowedOrigins);
    }

    /**
     * Get allowed HTTP methods
     */
    protected function getAllowedMethods(): string
    {
        return 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD';
    }

    /**
     * Get allowed headers
     */
    protected function getAllowedHeaders(): string
    {
        return implode(', ', [
            'Accept',
            'Authorization',
            'Content-Type',
            'X-Requested-With',
            'X-CSRF-TOKEN',
            'X-API-Version',
            'X-Client-Version',
            'Cache-Control',
            'Pragma',
        ]);
    }

    /**
     * Get exposed headers
     */
    protected function getExposedHeaders(): string
    {
        return implode(', ', [
            'X-RateLimit-Limit',
            'X-RateLimit-Remaining',
            'X-RateLimit-Reset',
            'X-Total-Count',
            'X-Page-Count',
            'X-Current-Page',
            'X-Per-Page',
            'X-API-Version',
        ]);
    }
}
