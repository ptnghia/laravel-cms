<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    const TTL_SHORT = 300;      // 5 minutes
    const TTL_MEDIUM = 1800;    // 30 minutes
    const TTL_LONG = 3600;      // 1 hour
    const TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Cache key prefixes
     */
    const PREFIX_POSTS = 'posts';
    const PREFIX_CATEGORIES = 'categories';
    const PREFIX_TAGS = 'tags';
    const PREFIX_USERS = 'users';
    const PREFIX_SETTINGS = 'settings';
    const PREFIX_MENUS = 'menus';

    /**
     * Get cached data or execute callback and cache result
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::warning('Cache remember failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            
            // Fallback to direct execution if cache fails
            return $callback();
        }
    }

    /**
     * Cache posts with automatic invalidation
     */
    public static function cachePost($postId, callable $callback, int $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_POSTS . ".{$postId}";
        return self::remember($key, $ttl, $callback);
    }

    /**
     * Cache post list with filters
     */
    public static function cachePostList(array $filters = [], int $ttl = self::TTL_SHORT)
    {
        $key = self::PREFIX_POSTS . '.list.' . md5(serialize($filters));
        return $key; // Return key for use with Cache::remember in controller
    }

    /**
     * Cache categories tree
     */
    public static function cacheCategoriesTree(callable $callback, int $ttl = self::TTL_LONG)
    {
        $key = self::PREFIX_CATEGORIES . '.tree';
        return self::remember($key, $ttl, $callback);
    }

    /**
     * Cache popular tags
     */
    public static function cachePopularTags(callable $callback, int $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_TAGS . '.popular';
        return self::remember($key, $ttl, $callback);
    }

    /**
     * Cache user profile
     */
    public static function cacheUserProfile($userId, callable $callback, int $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_USERS . ".profile.{$userId}";
        return self::remember($key, $ttl, $callback);
    }

    /**
     * Cache settings by group
     */
    public static function cacheSettings(string $group, callable $callback, int $ttl = self::TTL_VERY_LONG)
    {
        $key = self::PREFIX_SETTINGS . ".{$group}";
        return self::remember($key, $ttl, $callback);
    }

    /**
     * Cache menu by location
     */
    public static function cacheMenu(string $location, callable $callback, int $ttl = self::TTL_LONG)
    {
        $key = self::PREFIX_MENUS . ".{$location}";
        return self::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate post-related caches
     */
    public static function invalidatePost($postId): void
    {
        $keys = [
            self::PREFIX_POSTS . ".{$postId}",
            self::PREFIX_POSTS . '.list.*', // Pattern for list caches
        ];

        self::forgetKeys($keys);
        self::forgetPattern(self::PREFIX_POSTS . '.list.*');
    }

    /**
     * Invalidate category-related caches
     */
    public static function invalidateCategory($categoryId): void
    {
        $keys = [
            self::PREFIX_CATEGORIES . ".{$categoryId}",
            self::PREFIX_CATEGORIES . '.tree',
            self::PREFIX_POSTS . '.list.*', // Posts lists may be filtered by category
        ];

        self::forgetKeys($keys);
        self::forgetPattern(self::PREFIX_POSTS . '.list.*');
    }

    /**
     * Invalidate tag-related caches
     */
    public static function invalidateTag($tagId): void
    {
        $keys = [
            self::PREFIX_TAGS . ".{$tagId}",
            self::PREFIX_TAGS . '.popular',
            self::PREFIX_POSTS . '.list.*', // Posts lists may be filtered by tags
        ];

        self::forgetKeys($keys);
        self::forgetPattern(self::PREFIX_POSTS . '.list.*');
    }

    /**
     * Invalidate user-related caches
     */
    public static function invalidateUser($userId): void
    {
        $keys = [
            self::PREFIX_USERS . ".profile.{$userId}",
            self::PREFIX_POSTS . '.list.*', // Posts lists may be filtered by author
        ];

        self::forgetKeys($keys);
        self::forgetPattern(self::PREFIX_POSTS . '.list.*');
    }

    /**
     * Invalidate settings caches
     */
    public static function invalidateSettings(string $group = null): void
    {
        if ($group) {
            Cache::forget(self::PREFIX_SETTINGS . ".{$group}");
        } else {
            self::forgetPattern(self::PREFIX_SETTINGS . '.*');
        }
    }

    /**
     * Invalidate menu caches
     */
    public static function invalidateMenu(string $location = null): void
    {
        if ($location) {
            Cache::forget(self::PREFIX_MENUS . ".{$location}");
        } else {
            self::forgetPattern(self::PREFIX_MENUS . '.*');
        }
    }

    /**
     * Forget multiple cache keys
     */
    protected static function forgetKeys(array $keys): void
    {
        foreach ($keys as $key) {
            if (!str_contains($key, '*')) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Forget cache keys by pattern (Redis only)
     */
    protected static function forgetPattern(string $pattern): void
    {
        try {
            // This works with Redis cache driver
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Cache pattern forget failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear all application caches
     */
    public static function clearAll(): void
    {
        try {
            Cache::flush();
            Log::info('All caches cleared successfully');
        } catch (\Exception $e) {
            Log::error('Failed to clear all caches', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array
    {
        try {
            $driver = config('cache.default');
            $stats = [
                'driver' => $driver,
                'status' => 'healthy',
            ];

            // Test cache functionality
            $testKey = 'cache_test_' . time();
            $testValue = 'test_value';
            
            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);
            
            if ($retrieved === $testValue) {
                $stats['test'] = 'passed';
            } else {
                $stats['test'] = 'failed';
                $stats['status'] = 'unhealthy';
            }

            return $stats;
        } catch (\Exception $e) {
            return [
                'driver' => config('cache.default'),
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Warm up essential caches
     */
    public static function warmUp(): array
    {
        $warmed = [];

        try {
            // Warm up categories tree
            $warmed['categories_tree'] = self::cacheCategoriesTree(function () {
                return \App\Models\Category::with('children')
                    ->where('is_active', true)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->get();
            });

            // Warm up popular tags
            $warmed['popular_tags'] = self::cachePopularTags(function () {
                return \App\Models\Tag::orderBy('usage_count', 'desc')
                    ->limit(20)
                    ->get();
            });

            // Warm up public settings
            $warmed['public_settings'] = self::cacheSettings('public', function () {
                return \App\Models\Setting::where('group', 'public')
                    ->pluck('value', 'key');
            });

            Log::info('Cache warm-up completed', ['warmed' => array_keys($warmed)]);
            
        } catch (\Exception $e) {
            Log::error('Cache warm-up failed', ['error' => $e->getMessage()]);
        }

        return $warmed;
    }
}
