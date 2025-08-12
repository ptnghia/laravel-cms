<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get dashboard data for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get role-specific dashboard data
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return $this->getAdminDashboard();
        } elseif ($user->hasRole('editor')) {
            return $this->getEditorDashboard($user);
        } elseif ($user->hasRole('author')) {
            return $this->getAuthorDashboard($user);
        } else {
            return $this->getUserDashboard($user);
        }
    }

    /**
     * Get quick stats for dashboard widgets.
     */
    public function quickStats(): JsonResponse
    {
        $stats = [
            'posts' => [
                'total' => Post::count(),
                'published' => Post::where('status', 'published')->count(),
                'drafts' => Post::where('status', 'draft')->count(),
                'today' => Post::whereDate('created_at', today())->count(),
            ],
            'comments' => [
                'total' => Comment::count(),
                'approved' => Comment::where('status', 'approved')->count(),
                'pending' => Comment::where('status', 'pending')->count(),
                'today' => Comment::whereDate('created_at', today())->count(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'new_today' => User::whereDate('created_at', today())->count(),
                'online' => User::where('last_login_at', '>=', now()->subMinutes(15))->count(),
            ],
            'system' => [
                'total_views' => Post::sum('view_count'),
                'avg_rating' => Post::where('rating_count', '>', 0)->avg('rating_avg'),
                'storage_used' => $this->getStorageUsed(),
                'uptime' => $this->getSystemUptime(),
            ],
        ];

        return $this->successResponse(
            ['stats' => $stats],
            'Quick stats retrieved successfully'
        );
    }

    /**
     * Get recent activities.
     */
    public function recentActivities(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 20), 50);

        $activities = [
            'recent_posts' => Post::with(['author', 'category'])
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function ($post) {
                    return [
                        'type' => 'post',
                        'title' => $post->title,
                        'author' => $post->author->name,
                        'status' => $post->status,
                        'created_at' => $post->created_at->toISOString(),
                        'url' => url("/admin/posts/{$post->id}"),
                    ];
                }),
            'recent_comments' => Comment::with(['user', 'commentable'])
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function ($comment) {
                    return [
                        'type' => 'comment',
                        'content' => \Str::limit($comment->content, 100),
                        'author' => $comment->user ? $comment->user->name : $comment->author_name,
                        'status' => $comment->status,
                        'created_at' => $comment->created_at->toISOString(),
                        'url' => url("/admin/comments/{$comment->id}"),
                    ];
                }),
            'recent_users' => User::with(['roles'])
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function ($user) {
                    return [
                        'type' => 'user',
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('display_name'),
                        'created_at' => $user->created_at->toISOString(),
                        'url' => url("/admin/users/{$user->id}"),
                    ];
                }),
        ];

        return $this->successResponse(
            ['activities' => $activities],
            'Recent activities retrieved successfully'
        );
    }

    /**
     * Get admin dashboard data.
     */
    private function getAdminDashboard(): JsonResponse
    {
        $data = [
            'overview' => [
                'total_posts' => Post::count(),
                'total_users' => User::count(),
                'total_comments' => Comment::count(),
                'pending_comments' => Comment::where('status', 'pending')->count(),
                'total_views' => Post::sum('view_count'),
            ],
            'recent_posts' => Post::with(['author'])->latest()->limit(5)->get(),
            'pending_comments' => Comment::with(['user', 'commentable'])
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get(),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'database_size' => $this->getDatabaseSize(),
                'storage_used' => $this->getStorageUsed(),
            ],
        ];

        return $this->successResponse(
            ['dashboard' => $data],
            'Admin dashboard data retrieved successfully'
        );
    }

    /**
     * Get editor dashboard data.
     */
    private function getEditorDashboard(User $user): JsonResponse
    {
        $data = [
            'overview' => [
                'total_posts' => Post::count(),
                'my_posts' => Post::where('author_id', $user->id)->count(),
                'pending_comments' => Comment::where('status', 'pending')->count(),
                'total_views' => Post::sum('view_count'),
            ],
            'my_recent_posts' => Post::where('author_id', $user->id)->latest()->limit(5)->get(),
            'pending_comments' => Comment::with(['user', 'commentable'])
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return $this->successResponse(
            ['dashboard' => $data],
            'Editor dashboard data retrieved successfully'
        );
    }

    /**
     * Get author dashboard data.
     */
    private function getAuthorDashboard(User $user): JsonResponse
    {
        $data = [
            'overview' => [
                'my_posts' => Post::where('author_id', $user->id)->count(),
                'published_posts' => Post::where('author_id', $user->id)->where('status', 'published')->count(),
                'draft_posts' => Post::where('author_id', $user->id)->where('status', 'draft')->count(),
                'total_views' => Post::where('author_id', $user->id)->sum('view_count'),
                'total_comments' => Comment::whereHas('commentable', function ($query) use ($user) {
                    $query->where('author_id', $user->id);
                })->count(),
            ],
            'my_recent_posts' => Post::where('author_id', $user->id)->latest()->limit(5)->get(),
            'recent_comments' => Comment::with(['user'])
                ->whereHas('commentable', function ($query) use ($user) {
                    $query->where('author_id', $user->id);
                })
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return $this->successResponse(
            ['dashboard' => $data],
            'Author dashboard data retrieved successfully'
        );
    }

    /**
     * Get user dashboard data.
     */
    private function getUserDashboard(User $user): JsonResponse
    {
        $data = [
            'overview' => [
                'my_comments' => Comment::where('user_id', $user->id)->count(),
                'approved_comments' => Comment::where('user_id', $user->id)->where('status', 'approved')->count(),
                'pending_comments' => Comment::where('user_id', $user->id)->where('status', 'pending')->count(),
            ],
            'my_recent_comments' => Comment::with(['commentable'])
                ->where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
            'recommended_posts' => Post::where('status', 'published')
                ->orderBy('view_count', 'desc')
                ->limit(5)
                ->get(),
        ];

        return $this->successResponse(
            ['dashboard' => $data],
            'User dashboard data retrieved successfully'
        );
    }

    /**
     * Helper methods.
     */
    private function getStorageUsed(): string
    {
        $path = storage_path('app/public');
        if (!is_dir($path)) {
            return '0 B';
        }

        $size = 0;
        try {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                $size += $file->getSize();
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }

        return $this->formatBytes($size);
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

    private function getSystemUptime(): string
    {
        // This would typically come from system monitoring
        // For now, return application start time
        return 'Since ' . now()->subHours(24)->diffForHumans();
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
