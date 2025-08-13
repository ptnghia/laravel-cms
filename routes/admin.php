<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group.
|
*/

// Test route without auth
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/test', function () {
        return 'Admin routes working!';
    })->name('test');

    Route::get('/demo', function () {
        // Mock data for demo
        $stats = [
            'total_posts' => 25,
            'total_pages' => 8,
            'total_users' => 12,
            'total_media' => 156,
        ];

        $recent_activity = [
            [
                'type' => 'post',
                'title' => 'Welcome to Laravel CMS',
                'author' => 'Admin User',
                'status' => 'published',
                'date' => 'Aug 13, 2025',
            ],
            [
                'type' => 'page',
                'title' => 'About Us',
                'author' => 'Admin User',
                'status' => 'draft',
                'date' => 'Aug 12, 2025',
            ],
        ];

        return view('admin.dashboard', compact('stats', 'recent_activity'));
    })->name('demo');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Dashboard API endpoints
    Route::get('/api/stats', [DashboardController::class, 'stats'])->name('api.stats');
    Route::get('/api/recent-activity', [DashboardController::class, 'recentActivity'])->name('api.recent-activity');
    Route::get('/api/system-health', [DashboardController::class, 'systemHealth'])->name('api.system-health');

    // Posts Management
    Route::resource('posts', PostController::class);
    Route::post('posts/{post}/duplicate', [PostController::class, 'duplicate'])->name('posts.duplicate');
    Route::patch('posts/{post}/status', [PostController::class, 'updateStatus'])->name('posts.status');
    Route::post('posts/bulk-action', [PostController::class, 'bulkAction'])->name('posts.bulk-action');

    // Pages Management
    Route::resource('pages', PageController::class);
    Route::post('pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');
    Route::patch('pages/{page}/status', [PageController::class, 'updateStatus'])->name('pages.status');
    Route::post('pages/bulk-action', [PageController::class, 'bulkAction'])->name('pages.bulk-action');

    // Categories Management
    Route::resource('categories', CategoryController::class);
    Route::post('categories/bulk-action', [CategoryController::class, 'bulkAction'])->name('categories.bulk-action');

    // Tags Management
    Route::resource('tags', TagController::class);
    Route::post('tags/bulk-action', [TagController::class, 'bulkAction'])->name('tags.bulk-action');

    // Media Management
    Route::resource('media', MediaController::class);
    Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::post('media/bulk-upload', [MediaController::class, 'bulkUpload'])->name('media.bulk-upload');
    Route::post('media/bulk-action', [MediaController::class, 'bulkAction'])->name('media.bulk-action');
    Route::get('media/{media}/download', [MediaController::class, 'download'])->name('media.download');

    // Users Management
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    Route::post('users/{user}/send-verification', [UserController::class, 'sendVerification'])->name('users.send-verification');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // Settings Management
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/general', [SettingController::class, 'general'])->name('settings.general');
    Route::get('/settings/email', [SettingController::class, 'email'])->name('settings.email');
    Route::get('/settings/social', [SettingController::class, 'social'])->name('settings.social');
    Route::get('/settings/seo', [SettingController::class, 'seo'])->name('settings.seo');
    Route::get('/settings/advanced', [SettingController::class, 'advanced'])->name('settings.advanced');

    // System Management (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/system/cache', [SettingController::class, 'cache'])->name('system.cache');
        Route::post('/system/cache/clear', [SettingController::class, 'clearCache'])->name('system.cache.clear');
        Route::get('/system/logs', [SettingController::class, 'logs'])->name('system.logs');
        Route::get('/system/backup', [SettingController::class, 'backup'])->name('system.backup');
        Route::post('/system/backup/create', [SettingController::class, 'createBackup'])->name('system.backup.create');
    });

    // API Routes for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {

        // Search endpoints
        Route::get('/search/posts', [PostController::class, 'search'])->name('search.posts');
        Route::get('/search/pages', [PageController::class, 'search'])->name('search.pages');
        Route::get('/search/users', [UserController::class, 'search'])->name('search.users');
        Route::get('/search/media', [MediaController::class, 'search'])->name('search.media');

        // Quick actions
        Route::post('/quick/post', [PostController::class, 'quickCreate'])->name('quick.post');
        Route::post('/quick/page', [PageController::class, 'quickCreate'])->name('quick.page');

        // Auto-save endpoints
        Route::post('/autosave/post/{post?}', [PostController::class, 'autosave'])->name('autosave.post');
        Route::post('/autosave/page/{page?}', [PageController::class, 'autosave'])->name('autosave.page');

        // Media operations
        Route::post('/media/folder', [MediaController::class, 'createFolder'])->name('media.folder');
        Route::patch('/media/{media}/move', [MediaController::class, 'move'])->name('media.move');
        Route::patch('/media/{media}/rename', [MediaController::class, 'rename'])->name('media.rename');

        // Category/Tag operations
        Route::post('/categories/quick', [CategoryController::class, 'quickCreate'])->name('categories.quick');
        Route::post('/tags/quick', [TagController::class, 'quickCreate'])->name('tags.quick');
    });
});

// Public admin login routes (outside auth middleware)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', function () {
        return redirect()->route('login');
    })->name('login');
});
