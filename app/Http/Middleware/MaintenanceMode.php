<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    /**
     * Routes that are always accessible during maintenance
     */
    protected array $allowedRoutes = [
        'api/health',
        'api/status',
        'api/auth/login',
        'api/maintenance/status',
    ];

    /**
     * IP addresses that can bypass maintenance mode
     */
    protected array $allowedIps = [
        '127.0.0.1',
        '::1',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled
        if (!$this->isMaintenanceModeEnabled()) {
            return $next($request);
        }

        // Allow certain routes during maintenance
        if ($this->isRouteAllowed($request)) {
            return $next($request);
        }

        // Allow certain IP addresses
        if ($this->isIpAllowed($request)) {
            return $next($request);
        }

        // Allow super admins to bypass maintenance
        if ($this->canUserBypassMaintenance($request)) {
            return $next($request);
        }

        // Return maintenance response
        return $this->maintenanceResponse($request);
    }

    /**
     * Check if maintenance mode is enabled
     */
    protected function isMaintenanceModeEnabled(): bool
    {
        // Check Laravel's built-in maintenance mode first
        if (app()->isDownForMaintenance()) {
            return true;
        }

        // Check custom maintenance mode setting
        return Cache::get('maintenance_mode_enabled', false) || 
               config('app.maintenance_mode', false);
    }

    /**
     * Check if the current route is allowed during maintenance
     */
    protected function isRouteAllowed(Request $request): bool
    {
        $path = $request->path();
        
        foreach ($this->allowedRoutes as $allowedRoute) {
            if (str_starts_with($path, $allowedRoute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current IP is allowed during maintenance
     */
    protected function isIpAllowed(Request $request): bool
    {
        $clientIp = $request->ip();
        
        // Get allowed IPs from config and merge with default
        $configAllowedIps = config('maintenance.allowed_ips', []);
        $allowedIps = array_merge($this->allowedIps, $configAllowedIps);
        
        return in_array($clientIp, $allowedIps);
    }

    /**
     * Check if the current user can bypass maintenance mode
     */
    protected function canUserBypassMaintenance(Request $request): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Allow super admins to bypass maintenance
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Check if user has specific maintenance bypass permission
        if ($user->hasPermission('bypass_maintenance')) {
            return true;
        }

        // Check if user ID is in allowed list
        $allowedUserIds = config('maintenance.allowed_user_ids', []);
        if (in_array($user->id, $allowedUserIds)) {
            return true;
        }

        return false;
    }

    /**
     * Return maintenance mode response
     */
    protected function maintenanceResponse(Request $request): Response
    {
        $maintenanceData = $this->getMaintenanceData();
        
        // Return JSON response for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $maintenanceData['message'],
                'error' => [
                    'code' => 503,
                    'type' => 'MAINTENANCE_MODE',
                    'estimated_duration' => $maintenanceData['estimated_duration'],
                    'retry_after' => $maintenanceData['retry_after'],
                    'contact_email' => $maintenanceData['contact_email'],
                ],
                'maintenance' => [
                    'start_time' => $maintenanceData['start_time'],
                    'end_time' => $maintenanceData['end_time'],
                    'reason' => $maintenanceData['reason'],
                    'progress' => $maintenanceData['progress'],
                ],
                'timestamp' => now()->toISOString(),
                'status_code' => 503
            ], 503, [
                'Retry-After' => $maintenanceData['retry_after'],
                'X-Maintenance-Mode' => 'true',
            ]);
        }

        // Return HTML response for web requests
        return response()->view('maintenance', $maintenanceData, 503, [
            'Retry-After' => $maintenanceData['retry_after'],
            'X-Maintenance-Mode' => 'true',
        ]);
    }

    /**
     * Get maintenance mode data
     */
    protected function getMaintenanceData(): array
    {
        $cached = Cache::get('maintenance_mode_data', []);
        
        return array_merge([
            'message' => 'Hệ thống đang được bảo trì. Vui lòng thử lại sau.',
            'reason' => 'Bảo trì định kỳ',
            'start_time' => now()->toISOString(),
            'end_time' => now()->addHours(2)->toISOString(),
            'estimated_duration' => '2 giờ',
            'retry_after' => 3600, // 1 hour in seconds
            'progress' => 0, // 0-100%
            'contact_email' => config('mail.from.address', 'admin@laravel-cms.com'),
        ], $cached);
    }

    /**
     * Enable maintenance mode
     */
    public static function enable(array $data = []): void
    {
        Cache::put('maintenance_mode_enabled', true);
        
        if (!empty($data)) {
            Cache::put('maintenance_mode_data', $data);
        }
    }

    /**
     * Disable maintenance mode
     */
    public static function disable(): void
    {
        Cache::forget('maintenance_mode_enabled');
        Cache::forget('maintenance_mode_data');
    }

    /**
     * Update maintenance progress
     */
    public static function updateProgress(int $progress, string $message = null): void
    {
        $data = Cache::get('maintenance_mode_data', []);
        $data['progress'] = max(0, min(100, $progress));
        
        if ($message) {
            $data['message'] = $message;
        }
        
        Cache::put('maintenance_mode_data', $data);
    }

    /**
     * Check if maintenance mode is currently active
     */
    public static function isActive(): bool
    {
        return Cache::get('maintenance_mode_enabled', false) || 
               app()->isDownForMaintenance();
    }
}
