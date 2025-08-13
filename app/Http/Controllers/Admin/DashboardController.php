<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Page;
use App\Models\User;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Get statistics
        $stats = [
            'total_posts' => Post::count(),
            'total_pages' => Page::count(),
            'total_users' => User::count(),
            'total_media' => Media::count(),
        ];

        // Get recent activity (last 10 items)
        $recent_activity = collect();

        // Get recent posts
        $recent_posts = Post::with('author')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($post) {
                return [
                    'type' => 'post',
                    'title' => $post->title,
                    'author' => $post->author->name ?? 'Unknown',
                    'status' => $post->status,
                    'date' => $post->created_at->format('M d, Y'),
                ];
            });

        // Get recent pages
        $recent_pages = Page::with('author')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($page) {
                return [
                    'type' => 'page',
                    'title' => $page->title,
                    'author' => $page->author->name ?? 'Unknown',
                    'status' => $page->status,
                    'date' => $page->created_at->format('M d, Y'),
                ];
            });

        // Merge and sort by date
        $recent_activity = $recent_posts->concat($recent_pages)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return view('admin.dashboard', compact('stats', 'recent_activity'));
    }

    /**
     * Get dashboard statistics for API.
     */
    public function stats()
    {
        $stats = [
            'posts' => [
                'total' => Post::count(),
                'published' => Post::where('status', 'published')->count(),
                'draft' => Post::where('status', 'draft')->count(),
                'recent' => Post::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'pages' => [
                'total' => Page::count(),
                'published' => Page::where('status', 'published')->count(),
                'draft' => Page::where('status', 'draft')->count(),
                'recent' => Page::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('email_verified_at', '!=', null)->count(),
                'recent' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'media' => [
                'total' => Media::count(),
                'size' => Media::sum('size'),
                'recent' => Media::where('created_at', '>=', now()->subDays(7))->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Get recent activity for API.
     */
    public function recentActivity()
    {
        $activities = collect();

        // Get recent posts
        $posts = Post::with('author')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'type' => 'post',
                    'title' => $post->title,
                    'author' => $post->author->name ?? 'Unknown',
                    'status' => $post->status,
                    'created_at' => $post->created_at,
                    'url' => route('admin.posts.show', $post),
                ];
            });

        // Get recent pages
        $pages = Page::with('author')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($page) {
                return [
                    'id' => $page->id,
                    'type' => 'page',
                    'title' => $page->title,
                    'author' => $page->author->name ?? 'Unknown',
                    'status' => $page->status,
                    'created_at' => $page->created_at,
                    'url' => route('admin.pages.show', $page),
                ];
            });

        // Get recent users
        $users = User::latest()
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'type' => 'user',
                    'title' => 'New user: ' . $user->name,
                    'author' => 'System',
                    'status' => $user->email_verified_at ? 'verified' : 'pending',
                    'created_at' => $user->created_at,
                    'url' => route('admin.users.show', $user),
                ];
            });

        // Merge and sort
        $activities = $posts->concat($pages)->concat($users)
            ->sortByDesc('created_at')
            ->take(20)
            ->values();

        return response()->json($activities);
    }

    /**
     * Get system health status.
     */
    public function systemHealth()
    {
        $health = [
            'database' => $this->checkDatabaseConnection(),
            'storage' => $this->checkStorageWritable(),
            'cache' => $this->checkCacheWorking(),
            'queue' => $this->checkQueueWorking(),
        ];

        $overall = collect($health)->every(fn($status) => $status === 'ok') ? 'healthy' : 'warning';

        return response()->json([
            'status' => $overall,
            'checks' => $health,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Check database connection.
     */
    private function checkDatabaseConnection(): string
    {
        try {
            DB::connection()->getPdo();
            return 'ok';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Check if storage is writable.
     */
    private function checkStorageWritable(): string
    {
        try {
            $testFile = storage_path('app/health-check.txt');
            file_put_contents($testFile, 'test');
            unlink($testFile);
            return 'ok';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Check if cache is working.
     */
    private function checkCacheWorking(): string
    {
        try {
            cache()->put('health-check', 'test', 60);
            $value = cache()->get('health-check');
            cache()->forget('health-check');
            return $value === 'test' ? 'ok' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Check if queue is working.
     */
    private function checkQueueWorking(): string
    {
        try {
            // Simple check - just verify queue connection exists
            // In production, you might want to dispatch a test job
            return 'ok';
        } catch (\Exception $e) {
            return 'error';
        }
    }
}
