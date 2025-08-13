<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersioning
{
    /**
     * Supported API versions
     */
    protected array $supportedVersions = ['v1', 'v2'];

    /**
     * Default API version
     */
    protected string $defaultVersion = 'v1';

    /**
     * Deprecated versions with sunset dates
     */
    protected array $deprecatedVersions = [
        // 'v1' => '2025-12-31', // Example: v1 will be deprecated on 2025-12-31
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $version = $this->resolveApiVersion($request);
        
        // Validate version
        if (!$this->isVersionSupported($version)) {
            return $this->unsupportedVersionResponse($version);
        }

        // Set version in request for controllers to use
        $request->attributes->set('api_version', $version);
        
        // Set version in app container for global access
        app()->instance('api.version', $version);

        $response = $next($request);

        // Add version headers to response
        return $this->addVersionHeaders($response, $version);
    }

    /**
     * Resolve API version from request
     */
    protected function resolveApiVersion(Request $request): string
    {
        // 1. Check Accept header (preferred method)
        $acceptHeader = $request->header('Accept');
        if ($acceptHeader && preg_match('/application\/vnd\.laravel-cms\.(\w+)\+json/', $acceptHeader, $matches)) {
            return $matches[1];
        }

        // 2. Check X-API-Version header
        $versionHeader = $request->header('X-API-Version');
        if ($versionHeader) {
            return $this->normalizeVersion($versionHeader);
        }

        // 3. Check query parameter
        $queryVersion = $request->query('version');
        if ($queryVersion) {
            return $this->normalizeVersion($queryVersion);
        }

        // 4. Check URL path prefix (e.g., /api/v1/posts)
        $pathVersion = $this->extractVersionFromPath($request->path());
        if ($pathVersion) {
            return $pathVersion;
        }

        // 5. Return default version
        return $this->defaultVersion;
    }

    /**
     * Normalize version string
     */
    protected function normalizeVersion(string $version): string
    {
        // Remove 'v' prefix if present and add it back
        $version = ltrim(strtolower($version), 'v');
        return 'v' . $version;
    }

    /**
     * Extract version from URL path
     */
    protected function extractVersionFromPath(string $path): ?string
    {
        if (preg_match('/^api\/(v\d+)\//', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if version is supported
     */
    protected function isVersionSupported(string $version): bool
    {
        return in_array($version, $this->supportedVersions);
    }

    /**
     * Return unsupported version response
     */
    protected function unsupportedVersionResponse(string $version): Response
    {
        return response()->json([
            'success' => false,
            'message' => "Phiên bản API '{$version}' không được hỗ trợ.",
            'error' => [
                'code' => 400,
                'type' => 'UNSUPPORTED_API_VERSION',
                'requested_version' => $version,
                'supported_versions' => $this->supportedVersions,
                'default_version' => $this->defaultVersion
            ],
            'timestamp' => now()->toISOString(),
            'status_code' => 400
        ], 400);
    }

    /**
     * Add version headers to response
     */
    protected function addVersionHeaders(Response $response, string $version): Response
    {
        $response->headers->set('X-API-Version', $version);
        $response->headers->set('X-Supported-Versions', implode(', ', $this->supportedVersions));

        // Add deprecation warning if version is deprecated
        if (isset($this->deprecatedVersions[$version])) {
            $sunsetDate = $this->deprecatedVersions[$version];
            $response->headers->set('Sunset', $sunsetDate);
            $response->headers->set('Deprecation', 'true');
            $response->headers->set('Link', '<https://docs.laravel-cms.com/api/migration>; rel="successor-version"');
        }

        return $response;
    }

    /**
     * Get current API version from container
     */
    public static function getCurrentVersion(): string
    {
        return app('api.version', 'v1');
    }

    /**
     * Check if current version is deprecated
     */
    public static function isCurrentVersionDeprecated(): bool
    {
        $version = static::getCurrentVersion();
        $middleware = new static();
        
        return isset($middleware->deprecatedVersions[$version]);
    }
}
