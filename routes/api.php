<?php

use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AnalyticsController;
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

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Public content routes (read-only)
Route::prefix('public')->group(function () {
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

    // Tags
    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{id}', [TagController::class, 'show']);
    Route::get('tags/slug/{slug}', [TagController::class, 'getBySlug']);
    Route::get('tags/popular', [TagController::class, 'popular']);
    Route::get('tags/search', [TagController::class, 'search']);

    // Comments (read-only)
    Route::get('comments/{type}/{id}', [CommentController::class, 'getForResource']);

    // Public comment submission
    Route::post('comments', [CommentController::class, 'store']);

    // Public menus
    Route::get('menus', [MenuController::class, 'index']);
    Route::get('menus/{id}', [MenuController::class, 'show']);
    Route::get('menus/location/{location}', [MenuController::class, 'getByLocation']);
});

// Public settings endpoint (outside auth middleware)
Route::get('settings/public', [SettingController::class, 'public']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('password', [ProfileController::class, 'updatePassword']);
        Route::post('avatar', [ProfileController::class, 'uploadAvatar']);
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

    // Media routes
    Route::apiResource('media', MediaController::class);

    // Admin routes (require admin or super_admin role)
    Route::prefix('admin')->middleware('role:super_admin,admin')->group(function () {
        // User management
        Route::apiResource('users', UserController::class);
        Route::post('users/{id}/assign-roles', [UserController::class, 'assignRoles']);
        Route::post('users/{id}/suspend', [UserController::class, 'suspend']);
        Route::post('users/{id}/activate', [UserController::class, 'activate']);
        Route::get('users/statistics', [UserController::class, 'statistics']);

        // Role management
        Route::apiResource('roles', RoleController::class);
        Route::get('roles/permissions', [RoleController::class, 'permissions']);
        Route::post('roles/{id}/assign-permissions', [RoleController::class, 'assignPermissions']);

        // Settings management
        Route::apiResource('settings', SettingController::class);
        Route::get('settings/groups', [SettingController::class, 'groups']);
        Route::post('settings/bulk-update', [SettingController::class, 'bulkUpdate']);

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
