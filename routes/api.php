<?php

use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\ThemeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes with rate limiting
Route::prefix('auth')->middleware('auth.limited')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Public content routes (read-only) with API rate limiting
Route::prefix('public')->middleware('api.limited')->group(function () {
    // Posts
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{id}', [PostController::class, 'show']);
    Route::get('posts/slug/{slug}', [PostController::class, 'getBySlug']);

    // Pages
    Route::get('pages', [PageController::class, 'index']);
    Route::get('pages/{id}', [PageController::class, 'show']);
    Route::get('pages/slug/{slug}', [PageController::class, 'getBySlug']);
    Route::get('pages/tree', [PageController::class, 'tree']);

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::get('categories/slug/{slug}', [CategoryController::class, 'getBySlug']);
    Route::get('categories/tree', [CategoryController::class, 'tree']);

    // Tags with search rate limiting
    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{id}', [TagController::class, 'show']);
    Route::get('tags/slug/{slug}', [TagController::class, 'getBySlug']);
    Route::get('tags/popular', [TagController::class, 'popular']);
    Route::get('tags/search', [TagController::class, 'search'])->middleware('search.limited');

    // Comments (read-only)
    Route::get('comments/{type}/{id}', [CommentController::class, 'getForResource']);

    // Public comment submission with rate limiting
    Route::post('comments', [CommentController::class, 'store'])->middleware('auth.limited');

    // Public menus
    Route::get('menus', [MenuController::class, 'index']);
    Route::get('menus/{id}', [MenuController::class, 'show']);
    Route::get('menus/location/{location}', [MenuController::class, 'getByLocation']);
});

// Public settings endpoint (outside auth middleware)
Route::get('settings/public', [SettingController::class, 'public']);

// Health check and status endpoints (no rate limiting)
Route::get('health', [SystemController::class, 'health']);
Route::get('status', [SystemController::class, 'status']);
Route::get('ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'pong',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'api_version' => app()->bound('api.version') ? app('api.version') : 'v1',
    ]);
});

// Maintenance status endpoint
Route::get('maintenance/status', function () {
    return response()->json([
        'success' => true,
        'maintenance_mode' => \App\Http\Middleware\MaintenanceMode::isActive(),
        'timestamp' => now()->toISOString(),
    ]);
});

// API Documentation routes
Route::prefix('docs')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'success' => true,
            'message' => 'Laravel CMS API Documentation',
            'version' => config('app.version', '1.0.0'),
            'api_version' => app()->bound('api.version') ? app('api.version') : 'v1',
            'supported_versions' => ['v1', 'v2'],
            'endpoints' => [
                'authentication' => '/api/auth/*',
                'public_content' => '/api/public/*',
                'user_content' => '/api/{posts,pages,categories,tags,comments,media}',
                'admin_panel' => '/api/admin/*',
                'documentation' => '/api/docs/*',
            ],
            'rate_limits' => [
                'auth' => '5-10 requests per 15 minutes',
                'api' => '60-1000 requests per minute',
                'upload' => '5-50 requests per hour',
                'search' => '50-200 requests per minute',
            ],
            'timestamp' => now()->toISOString(),
        ]);
    });

    Route::get('openapi', function () {
        return response()->json([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Laravel CMS API',
                'description' => 'RESTful API for Laravel CMS',
                'version' => config('app.version', '1.0.0'),
                'contact' => [
                    'email' => config('mail.from.address', 'admin@laravel-cms.com'),
                ],
            ],
            'servers' => [
                [
                    'url' => config('app.url') . '/api',
                    'description' => 'API Server',
                ],
            ],
            'paths' => [
                // Will be populated by Swagger/OpenAPI generator
            ],
        ]);
    });

    Route::get('postman', function () {
        return response()->json([
            'info' => [
                'name' => 'Laravel CMS API',
                'description' => 'Postman collection for Laravel CMS API',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'variable' => [
                [
                    'key' => 'baseUrl',
                    'value' => config('app.url') . '/api',
                ],
                [
                    'key' => 'token',
                    'value' => 'YOUR_API_TOKEN_HERE',
                ],
            ],
            'item' => [
                // Will be populated by collection generator
            ],
        ]);
    });
});

// Protected routes with authentication
Route::middleware(['auth:sanctum', 'api.limited'])->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Profile routes with upload rate limiting for avatar
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('password', [ProfileController::class, 'updatePassword']);
        Route::post('avatar', [ProfileController::class, 'uploadAvatar'])->middleware('upload.limited');
        Route::delete('avatar', [ProfileController::class, 'deleteAvatar']);
        Route::delete('account', [ProfileController::class, 'deleteAccount']);
    });

    // Content API routes
    Route::apiResource('posts', PostController::class);
    Route::post('posts/{id}/publish', [PostController::class, 'publish']);
    Route::post('posts/{id}/unpublish', [PostController::class, 'unpublish']);
    Route::post('posts/{id}/duplicate', [PostController::class, 'duplicate']);
    Route::get('posts/slug/{slug}', [PostController::class, 'getBySlug']);

    Route::apiResource('pages', PageController::class);
    Route::get('pages/slug/{slug}', [PageController::class, 'getBySlug']);
    Route::get('pages/tree', [PageController::class, 'tree']);

    Route::apiResource('categories', CategoryController::class);
    Route::get('categories/slug/{slug}', [CategoryController::class, 'getBySlug']);
    Route::get('categories/tree', [CategoryController::class, 'tree']);

    Route::apiResource('tags', TagController::class);
    Route::get('tags/slug/{slug}', [TagController::class, 'getBySlug']);
    Route::get('tags/popular', [TagController::class, 'popular']);
    Route::get('tags/search', [TagController::class, 'search']);

    Route::apiResource('comments', CommentController::class);
    Route::post('comments/{id}/approve', [CommentController::class, 'approve']);
    Route::post('comments/{id}/spam', [CommentController::class, 'spam']);
    Route::get('comments/{type}/{id}', [CommentController::class, 'getForResource']);

    // Media routes with upload rate limiting
    Route::apiResource('media', MediaController::class)->middleware('upload.limited');

    // Admin routes with role and permission-based authorization
    Route::prefix('admin')->middleware('role:super_admin,admin')->group(function () {
        // User management (requires users permissions)
        Route::middleware('permission:users.view')->group(function () {
            Route::get('users', [UserController::class, 'index']);
            Route::get('users/{id}', [UserController::class, 'show']);
            Route::get('users/statistics', [UserController::class, 'statistics']);
        });

        Route::middleware('permission:users.create')->group(function () {
            Route::post('users', [UserController::class, 'store']);
        });

        Route::middleware('permission:users.edit')->group(function () {
            Route::put('users/{id}', [UserController::class, 'update']);
            Route::patch('users/{id}', [UserController::class, 'update']);
            Route::post('users/{id}/assign-roles', [UserController::class, 'assignRoles']);
            Route::post('users/{id}/suspend', [UserController::class, 'suspend']);
            Route::post('users/{id}/activate', [UserController::class, 'activate']);
        });

        Route::middleware('permission:users.delete')->group(function () {
            Route::delete('users/{id}', [UserController::class, 'destroy']);
        });

        // Role management (requires roles permissions)
        Route::middleware('permission:roles.view')->group(function () {
            Route::get('roles', [RoleController::class, 'index']);
            Route::get('roles/{id}', [RoleController::class, 'show']);
            Route::get('roles/permissions', [RoleController::class, 'permissions']);
        });

        Route::middleware('permission:roles.create')->group(function () {
            Route::post('roles', [RoleController::class, 'store']);
        });

        Route::middleware('permission:roles.edit')->group(function () {
            Route::put('roles/{id}', [RoleController::class, 'update']);
            Route::patch('roles/{id}', [RoleController::class, 'update']);
            Route::post('roles/{id}/assign-permissions', [RoleController::class, 'assignPermissions']);
        });

        Route::middleware('permission:roles.delete')->group(function () {
            Route::delete('roles/{id}', [RoleController::class, 'destroy']);
        });

        // Settings management (requires settings permissions)
        Route::middleware('permission:settings.view')->group(function () {
            Route::get('settings', [SettingController::class, 'index']);
            Route::get('settings/{id}', [SettingController::class, 'show']);
            Route::get('settings/groups', [SettingController::class, 'groups']);
        });

        Route::middleware('permission:settings.edit')->group(function () {
            Route::post('settings', [SettingController::class, 'store']);
            Route::put('settings/{id}', [SettingController::class, 'update']);
            Route::patch('settings/{id}', [SettingController::class, 'update']);
            Route::post('settings/bulk-update', [SettingController::class, 'bulkUpdate']);
        });

        Route::middleware('permission:settings.delete')->group(function () {
            Route::delete('settings/{id}', [SettingController::class, 'destroy']);
        });

        // System management (super admin only)
        Route::middleware('role:super_admin')->group(function () {
            Route::get('system/info', [SystemController::class, 'info']);
            Route::post('system/cache/clear', [SystemController::class, 'clearCache']);
            Route::post('system/optimize', [SystemController::class, 'optimize']);
            Route::post('system/maintenance', [SystemController::class, 'maintenance']);

            // Activity logs
            Route::get('activity-logs', [ActivityLogController::class, 'index']);
            Route::get('activity-logs/statistics', [ActivityLogController::class, 'statistics']);
            Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show']);
            Route::delete('activity-logs/{activityLog}', [ActivityLogController::class, 'destroy']);
            Route::post('activity-logs/cleanup', [ActivityLogController::class, 'cleanup']);
        });

        // Dashboard & Analytics
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('dashboard/quick-stats', [DashboardController::class, 'quickStats']);
        Route::get('dashboard/recent-activities', [DashboardController::class, 'recentActivities']);

        Route::get('analytics/overview', [AnalyticsController::class, 'overview']);
        Route::get('analytics/content', [AnalyticsController::class, 'content']);
        Route::get('analytics/users', [AnalyticsController::class, 'users']);
        Route::get('analytics/system', [AnalyticsController::class, 'system']);

        // System Management
        Route::get('system/info', [SystemController::class, 'info']);
        Route::post('system/clear-cache', [SystemController::class, 'clearCache']);
        Route::post('system/optimize', [SystemController::class, 'optimize']);
        Route::post('system/maintenance', [SystemController::class, 'maintenance']);
        Route::get('system/health', [SystemController::class, 'health']);

        // Menu Management
        Route::apiResource('menus', MenuController::class);
        Route::get('menus/location/{location}', [MenuController::class, 'getByLocation']);
        Route::post('menus/{menu}/structure', [MenuController::class, 'updateStructure']);
        Route::post('menus/{menu}/items', [MenuController::class, 'addItem']);
        Route::delete('menus/{menu}/items/{item}', [MenuController::class, 'removeItem']);
        Route::get('menu-locations', [MenuController::class, 'locations']);

        // Theme Management
        Route::get('themes', [ThemeController::class, 'index']);
        Route::get('themes/current', [ThemeController::class, 'current']);
        Route::post('themes/activate', [ThemeController::class, 'activate']);
        Route::get('themes/customization', [ThemeController::class, 'customization']);
        Route::post('themes/customization', [ThemeController::class, 'updateCustomization']);
        Route::post('themes/reset-customization', [ThemeController::class, 'resetCustomization']);

        // Backup Management
        Route::apiResource('backups', BackupController::class)->except(['update']);
        Route::get('backups/{backup}/download', [BackupController::class, 'download']);
        Route::get('backups/statistics', [BackupController::class, 'statistics']);
    });
});

// Test routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Admin only route
    Route::get('/admin/test', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Admin access granted',
            'user' => $request->user()->name,
        ]);
    })->middleware('role:super_admin,admin');

    // Permission-based route
    Route::get('/users/manage', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'User management access granted',
            'user' => $request->user()->name,
        ]);
    })->middleware('permission:users.view');
});
