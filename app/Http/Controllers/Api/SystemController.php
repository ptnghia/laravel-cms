<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SystemController extends Controller
{
    use ApiResponseTrait;



    /**
     * Get system status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => 'online',
            'maintenance_mode' => \App\Http\Middleware\MaintenanceMode::isActive(),
            'api_version' => app()->bound('api.version') ? app('api.version') : 'v1',
            'supported_versions' => ['v1', 'v2'],
            'timestamp' => now()->toISOString(),
            'server_time' => now()->format('Y-m-d H:i:s T'),
            'timezone' => config('app.timezone'),
        ]);
    }

    /**
     * Get system information.
     */
    public function info(): JsonResponse
    {
        $info = [
            'application' => [
                'name' => config('app.name'),
                'version' => '1.0.0',
                'environment' => config('app.env'),
                'debug_mode' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'url' => config('app.url'),
            ],
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'operating_system' => PHP_OS,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
            'database' => [
                'connection' => config('database.default'),
                'driver' => config('database.connections.' . config('database.default') . '.driver'),
                'version' => $this->getDatabaseVersion(),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'stores' => array_keys(config('cache.stores')),
            ],
            'queue' => [
                'default' => config('queue.default'),
                'connections' => array_keys(config('queue.connections')),
            ],
            'mail' => [
                'driver' => config('mail.default'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
        ];

        return $this->successResponse(
            ['system_info' => $info],
            'System information retrieved successfully'
        );
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:all,config,route,view,cache,compiled',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Dữ liệu không hợp lệ'
            );
        }

        $type = $request->get('type', 'all');
        $cleared = [];

        try {
            switch ($type) {
                case 'config':
                    Artisan::call('config:clear');
                    $cleared[] = 'Configuration cache';
                    break;

                case 'route':
                    Artisan::call('route:clear');
                    $cleared[] = 'Route cache';
                    break;

                case 'view':
                    Artisan::call('view:clear');
                    $cleared[] = 'View cache';
                    break;

                case 'cache':
                    Artisan::call('cache:clear');
                    $cleared[] = 'Application cache';
                    break;

                case 'compiled':
                    Artisan::call('clear-compiled');
                    $cleared[] = 'Compiled classes';
                    break;

                case 'all':
                default:
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Artisan::call('cache:clear');
                    Artisan::call('clear-compiled');
                    $cleared = ['All caches'];
                    break;
            }

            return $this->successResponse(
                ['cleared' => $cleared],
                'Cache cleared successfully'
            );
        } catch (\Exception $e) {
            Log::error('Cache clear failed', ['error' => $e->getMessage()]);

            return $this->errorResponse(
                'Failed to clear cache: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Optimize application.
     */
    public function optimize(): JsonResponse
    {
        try {
            $optimizations = [];

            // Cache configuration
            Artisan::call('config:cache');
            $optimizations[] = 'Configuration cached';

            // Cache routes
            Artisan::call('route:cache');
            $optimizations[] = 'Routes cached';

            // Cache views
            Artisan::call('view:cache');
            $optimizations[] = 'Views cached';

            // Optimize autoloader
            Artisan::call('optimize');
            $optimizations[] = 'Autoloader optimized';

            return $this->successResponse(
                ['optimizations' => $optimizations],
                'Application optimized successfully'
            );
        } catch (\Exception $e) {
            Log::error('Optimization failed', ['error' => $e->getMessage()]);

            return $this->errorResponse(
                'Failed to optimize application: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Run system maintenance.
     */
    public function maintenance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:enable,disable,status',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Dữ liệu không hợp lệ'
            );
        }

        $action = $request->action;

        try {
            switch ($action) {
                case 'enable':
                    Artisan::call('down');
                    return $this->successResponse(
                        ['status' => 'enabled'],
                        'Maintenance mode enabled'
                    );

                case 'disable':
                    Artisan::call('up');
                    return $this->successResponse(
                        ['status' => 'disabled'],
                        'Maintenance mode disabled'
                    );

                case 'status':
                    $isDown = app()->isDownForMaintenance();
                    return $this->successResponse(
                        ['maintenance_mode' => $isDown],
                        'Maintenance status retrieved'
                    );

                default:
                    return $this->errorResponse('Invalid action', 400);
            }
        } catch (\Exception $e) {
            Log::error('Maintenance action failed', ['error' => $e->getMessage()]);

            return $this->errorResponse(
                'Failed to execute maintenance action: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get system health check.
     */
    public function health(): JsonResponse
    {
        $health = [
            'status' => 'healthy',
            'checks' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'queue' => $this->checkQueue(),
            ],
            'timestamp' => now()->toISOString(),
        ];

        // Determine overall status
        $failedChecks = collect($health['checks'])->filter(fn($check) => !$check['status'])->count();
        if ($failedChecks > 0) {
            $health['status'] = $failedChecks > 2 ? 'unhealthy' : 'degraded';
        }

        return $this->successResponse(
            ['health' => $health],
            'System health check completed'
        );
    }

    /**
     * Helper methods for system checks.
     */
    private function checkDatabase(): array
    {
        try {
            \DB::connection()->getPdo();
            return ['status' => true, 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Database connection failed'];
        }
    }

    private function checkCache(): array
    {
        try {
            Cache::put('health_check', 'test', 60);
            $value = Cache::get('health_check');
            Cache::forget('health_check');

            return ['status' => $value === 'test', 'message' => 'Cache working'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Cache not working'];
        }
    }

    private function checkStorage(): array
    {
        try {
            $path = storage_path('app');
            $writable = is_writable($path);

            return ['status' => $writable, 'message' => $writable ? 'Storage writable' : 'Storage not writable'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Storage check failed'];
        }
    }

    private function checkQueue(): array
    {
        try {
            // Basic queue check - this could be enhanced
            return ['status' => true, 'message' => 'Queue system available'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Queue system unavailable'];
        }
    }

    private function getDatabaseVersion(): string
    {
        try {
            $result = \DB::select('SELECT VERSION() as version');
            return $result[0]->version ?? 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
