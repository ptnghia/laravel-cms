<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic data for performance tests
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /**
     * Test database query performance with indexes
     */
    public function test_database_query_performance(): void
    {
        // Create test data
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $posts = Post::factory(100)->create([
            'author_id' => $user->id,
            'category_id' => $category->id,
            'status' => 'published',
        ]);

        // Enable query logging
        DB::enableQueryLog();

        // Test query performance
        $startTime = microtime(true);
        
        $result = Post::where('status', 'published')
            ->where('category_id', $category->id)
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get();

        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Assertions
        $this->assertCount(10, $result);
        $this->assertLessThan(100, $queryTime, 'Query should complete in less than 100ms');
        $this->assertLessThanOrEqual(1, count($queries), 'Should use only 1 query (no N+1 problem)');
    }

    /**
     * Test API response time performance
     */
    public function test_api_response_time_performance(): void
    {
        // Create test data
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        Category::factory(10)->create();
        Post::factory(50)->create(['status' => 'published']);

        // Test multiple endpoints
        $endpoints = [
            '/api/public/posts',
            '/api/public/categories',
            '/api/public/tags',
        ];

        foreach ($endpoints as $endpoint) {
            $startTime = microtime(true);
            
            $response = $this->getJson($endpoint);
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);
            $this->assertLessThan(500, $responseTime, "Endpoint {$endpoint} should respond in less than 500ms");
        }
    }

    /**
     * Test cache performance
     */
    public function test_cache_performance(): void
    {
        // Create test data
        Category::factory(20)->create();

        // Test cache miss (first call)
        $startTime = microtime(true);
        
        $result1 = CacheService::cacheCategoriesTree(function () {
            return Category::with('children')
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get();
        });
        
        $endTime = microtime(true);
        $cacheMissTime = ($endTime - $startTime) * 1000;

        // Test cache hit (second call)
        $startTime = microtime(true);
        
        $result2 = CacheService::cacheCategoriesTree(function () {
            return Category::with('children')
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get();
        });
        
        $endTime = microtime(true);
        $cacheHitTime = ($endTime - $startTime) * 1000;

        // Assertions
        $this->assertEquals($result1->count(), $result2->count());
        $this->assertLessThan($cacheMissTime / 2, $cacheHitTime, 'Cache hit should be at least 2x faster than cache miss');
        $this->assertLessThan(50, $cacheHitTime, 'Cache hit should be very fast (< 50ms)');
    }

    /**
     * Test memory usage
     */
    public function test_memory_usage(): void
    {
        $initialMemory = memory_get_usage(true);

        // Create and process large dataset
        $posts = Post::factory(1000)->make();
        
        // Process posts (simulate heavy operation)
        $processedPosts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => substr($post->content, 0, 150),
            ];
        });

        $finalMemory = memory_get_usage(true);
        $memoryUsed = $finalMemory - $initialMemory;
        $memoryUsedMB = $memoryUsed / 1024 / 1024;

        // Assertions
        $this->assertCount(1000, $processedPosts);
        $this->assertLessThan(50, $memoryUsedMB, 'Memory usage should be less than 50MB for 1000 posts');
    }

    /**
     * Test concurrent request handling
     */
    public function test_concurrent_request_simulation(): void
    {
        // Create test data
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        Post::factory(20)->create(['status' => 'published']);

        // Simulate concurrent requests
        $responses = [];
        $startTime = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson('/api/public/posts?page=' . ($i + 1));
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        // Assertions
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }
        
        $this->assertLessThan(2000, $totalTime, '10 concurrent requests should complete in less than 2 seconds');
    }

    /**
     * Test database connection pooling
     */
    public function test_database_connection_efficiency(): void
    {
        DB::enableQueryLog();

        // Perform multiple database operations
        for ($i = 0; $i < 10; $i++) {
            User::count();
            Post::count();
            Category::count();
        }

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should have exactly 30 queries (3 per iteration)
        $this->assertCount(30, $queries);
        
        // Each query should be fast
        foreach ($queries as $query) {
            $this->assertLessThan(100, $query['time'], 'Each query should complete in less than 100ms');
        }
    }

    /**
     * Test cache invalidation performance
     */
    public function test_cache_invalidation_performance(): void
    {
        // Warm up cache
        $post = Post::factory()->create(['status' => 'published']);
        $category = $post->category;
        
        CacheService::cachePost($post->id, fn() => $post);
        CacheService::cacheCategoriesTree(fn() => Category::all());

        // Test invalidation performance
        $startTime = microtime(true);
        
        CacheService::invalidatePost($post->id);
        CacheService::invalidateCategory($category->id);
        
        $endTime = microtime(true);
        $invalidationTime = ($endTime - $startTime) * 1000;

        // Assertions
        $this->assertLessThan(100, $invalidationTime, 'Cache invalidation should be fast (< 100ms)');
    }

    /**
     * Test API rate limiting performance
     */
    public function test_rate_limiting_performance(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $responses = [];
        $startTime = microtime(true);

        // Make requests up to rate limit
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong_password'
            ]);
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        // First 5 should be normal responses, 6th should be rate limited
        $this->assertEquals(401, $responses[0]->getStatusCode()); // Invalid credentials
        $this->assertEquals(401, $responses[4]->getStatusCode()); // Still invalid credentials
        
        // Rate limiting should not significantly slow down responses
        $this->assertLessThan(1000, $totalTime, 'Rate limiting should not add significant overhead');
    }

    /**
     * Test search performance
     */
    public function test_search_performance(): void
    {
        // Create searchable content
        Post::factory(100)->create([
            'status' => 'published',
            'title' => 'Laravel CMS Performance Test',
            'content' => 'This is a test post for search performance testing with Laravel CMS.',
        ]);

        $startTime = microtime(true);
        
        $response = $this->getJson('/api/public/posts?search=Laravel');
        
        $endTime = microtime(true);
        $searchTime = ($endTime - $startTime) * 1000;

        // Assertions
        $response->assertStatus(200);
        $this->assertLessThan(300, $searchTime, 'Search should complete in less than 300ms');
        
        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data), 'Search should return results');
    }

    /**
     * Test file upload performance
     */
    public function test_file_upload_performance(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create a test file
        $file = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 1024, 768)->size(1000); // 1MB

        $startTime = microtime(true);
        
        $response = $this->postJson('/api/media', [
            'file' => $file,
            'alt_text' => 'Test image',
        ]);
        
        $endTime = microtime(true);
        $uploadTime = ($endTime - $startTime) * 1000;

        // Assertions
        $response->assertStatus(201);
        $this->assertLessThan(2000, $uploadTime, 'File upload should complete in less than 2 seconds');
    }
}
