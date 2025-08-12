<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get dashboard overview statistics.
     */
    public function overview(): JsonResponse
    {
        $stats = [
            'content' => [
                'total_posts' => Post::count(),
                'published_posts' => Post::where('status', 'published')->count(),
                'draft_posts' => Post::where('status', 'draft')->count(),
                'scheduled_posts' => Post::where('status', 'scheduled')->count(),
                'total_views' => Post::sum('view_count'),
                'total_comments' => Comment::where('status', 'approved')->count(),
                'pending_comments' => Comment::where('status', 'pending')->count(),
            ],
            'users' => [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'new_users_today' => User::whereDate('created_at', today())->count(),
                'new_users_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
                'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'media' => [
                'total_files' => Media::count(),
                'total_size' => Media::sum('size'),
                'total_size_human' => $this->formatBytes(Media::sum('size')),
                'images_count' => Media::where('file_type', 'image')->count(),
                'videos_count' => Media::where('file_type', 'video')->count(),
                'documents_count' => Media::where('file_type', 'document')->count(),
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'database_size' => $this->getDatabaseSize(),
                'storage_used' => $this->getStorageUsed(),
            ],
        ];

        return $this->successResponse(
            ['statistics' => $stats],
            'Dashboard statistics retrieved successfully'
        );
    }

    /**
     * Get content analytics.
     */
    public function content(Request $request): JsonResponse
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        $analytics = [
            'posts_over_time' => $this->getPostsOverTime($startDate),
            'popular_posts' => $this->getPopularPosts(),
            'posts_by_category' => $this->getPostsByCategory(),
            'posts_by_author' => $this->getPostsByAuthor(),
            'comments_over_time' => $this->getCommentsOverTime($startDate),
            'top_tags' => $this->getTopTags(),
            'content_performance' => $this->getContentPerformance(),
        ];

        return $this->successResponse(
            ['analytics' => $analytics],
            'Content analytics retrieved successfully'
        );
    }

    /**
     * Get user analytics.
     */
    public function users(Request $request): JsonResponse
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        $analytics = [
            'registrations_over_time' => $this->getRegistrationsOverTime($startDate),
            'users_by_role' => $this->getUsersByRole(),
            'active_users' => $this->getActiveUsers($startDate),
            'user_engagement' => $this->getUserEngagement(),
            'top_contributors' => $this->getTopContributors(),
        ];

        return $this->successResponse(
            ['analytics' => $analytics],
            'User analytics retrieved successfully'
        );
    }

    /**
     * Get system analytics.
     */
    public function system(): JsonResponse
    {
        $analytics = [
            'performance' => [
                'average_response_time' => $this->getAverageResponseTime(),
                'memory_usage' => memory_get_usage(true),
                'memory_usage_human' => $this->formatBytes(memory_get_usage(true)),
                'peak_memory' => memory_get_peak_usage(true),
                'peak_memory_human' => $this->formatBytes(memory_get_peak_usage(true)),
            ],
            'storage' => [
                'database_size' => $this->getDatabaseSize(),
                'media_storage' => $this->getStorageUsed(),
                'cache_size' => $this->getCacheSize(),
                'logs_size' => $this->getLogsSize(),
            ],
            'security' => [
                'failed_logins_today' => $this->getFailedLoginsToday(),
                'blocked_ips' => $this->getBlockedIPs(),
                'spam_comments' => Comment::where('status', 'spam')->count(),
            ],
        ];

        return $this->successResponse(
            ['analytics' => $analytics],
            'System analytics retrieved successfully'
        );
    }

    /**
     * Get posts created over time.
     */
    private function getPostsOverTime($startDate): array
    {
        return Post::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get popular posts by views.
     */
    private function getPopularPosts(): array
    {
        return Post::select('id', 'title', 'slug', 'view_count', 'comment_count')
            ->where('status', 'published')
            ->orderBy('view_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get posts count by category.
     */
    private function getPostsByCategory(): array
    {
        return DB::table('posts')
            ->join('categories', 'posts.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(*) as count'))
            ->where('posts.status', 'published')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get posts count by author.
     */
    private function getPostsByAuthor(): array
    {
        return DB::table('posts')
            ->join('users', 'posts.author_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as count'))
            ->where('posts.status', 'published')
            ->groupBy('users.id', 'users.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get comments created over time.
     */
    private function getCommentsOverTime($startDate): array
    {
        return Comment::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->where('status', 'approved')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get top tags by usage.
     */
    private function getTopTags(): array
    {
        return DB::table('tags')
            ->select('name', 'usage_count')
            ->where('usage_count', '>', 0)
            ->orderBy('usage_count', 'desc')
            ->limit(15)
            ->get()
            ->toArray();
    }

    /**
     * Get content performance metrics.
     */
    private function getContentPerformance(): array
    {
        return [
            'avg_views_per_post' => Post::where('status', 'published')->avg('view_count'),
            'avg_comments_per_post' => Post::where('status', 'published')->avg('comment_count'),
            'most_viewed_post' => Post::where('status', 'published')->orderBy('view_count', 'desc')->first(['title', 'view_count']),
            'most_commented_post' => Post::where('status', 'published')->orderBy('comment_count', 'desc')->first(['title', 'comment_count']),
        ];
    }

    /**
     * Get user registrations over time.
     */
    private function getRegistrationsOverTime($startDate): array
    {
        return User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get users count by role.
     */
    private function getUsersByRole(): array
    {
        return DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->select('roles.display_name as role', DB::raw('COUNT(*) as count'))
            ->groupBy('roles.id', 'roles.display_name')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get active users in period.
     */
    private function getActiveUsers($startDate): array
    {
        return [
            'total_active' => User::where('last_login_at', '>=', $startDate)->count(),
            'daily_active' => User::where('last_login_at', '>=', now()->subDay())->count(),
            'weekly_active' => User::where('last_login_at', '>=', now()->subWeek())->count(),
            'monthly_active' => User::where('last_login_at', '>=', now()->subMonth())->count(),
        ];
    }

    /**
     * Get user engagement metrics.
     */
    private function getUserEngagement(): array
    {
        return [
            'avg_posts_per_user' => User::withCount('posts')->avg('posts_count'),
            'avg_comments_per_user' => User::withCount('comments')->avg('comments_count'),
            'most_active_users' => User::withCount(['posts', 'comments'])
                ->orderByDesc('posts_count')
                ->limit(5)
                ->get(['name', 'posts_count', 'comments_count'])
                ->toArray(),
        ];
    }

    /**
     * Get top contributors.
     */
    private function getTopContributors(): array
    {
        return User::select('users.name', 'users.email')
            ->withCount(['posts as published_posts_count' => function ($query) {
                $query->where('status', 'published');
            }])
            ->withCount('comments')
            ->having('published_posts_count', '>', 0)
            ->orderBy('published_posts_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Helper methods for system metrics.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getDatabaseSize(): string
    {
        try {
            $size = DB::select("SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = ?", [config('database.connections.mysql.database')]);
            return $this->formatBytes($size[0]->size ?? 0);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getStorageUsed(): string
    {
        $path = storage_path('app/public');
        if (!is_dir($path)) {
            return '0 B';
        }

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $size += $file->getSize();
        }

        return $this->formatBytes($size);
    }

    private function getCacheSize(): string
    {
        $path = storage_path('framework/cache');
        if (!is_dir($path)) {
            return '0 B';
        }

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $size += $file->getSize();
        }

        return $this->formatBytes($size);
    }

    private function getLogsSize(): string
    {
        $path = storage_path('logs');
        if (!is_dir($path)) {
            return '0 B';
        }

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $size += $file->getSize();
        }

        return $this->formatBytes($size);
    }

    private function getAverageResponseTime(): string
    {
        // This would typically come from application performance monitoring
        // For now, return a placeholder
        return '150ms';
    }

    private function getFailedLoginsToday(): int
    {
        // This would typically come from authentication logs
        // For now, return a placeholder
        return 0;
    }

    private function getBlockedIPs(): int
    {
        // This would typically come from security logs
        // For now, return a placeholder
        return 0;
    }
}
