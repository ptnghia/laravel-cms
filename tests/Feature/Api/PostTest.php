<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

class PostTest extends BaseApiTest
{
    /** @test */
    public function guest_can_view_published_posts(): void
    {
        $posts = Post::factory()->count(3)->create(['status' => 'published']);

        $response = $this->apiGet('/api/public/posts');

        $this->assertApiSuccess($response);
        $this->assertPaginatedResponse($response);
        $response->assertJsonCount(3, 'data.data');
    }

    /** @test */
    public function guest_cannot_view_draft_posts(): void
    {
        Post::factory()->count(2)->create(['status' => 'published']);
        Post::factory()->count(3)->create(['status' => 'draft']);

        $response = $this->apiGet('/api/public/posts');

        $this->assertApiSuccess($response);
        $response->assertJsonCount(2, 'data.data');
    }

    /** @test */
    public function guest_can_view_single_published_post(): void
    {
        $post = Post::factory()->create(['status' => 'published']);

        $response = $this->apiGet("/api/public/posts/{$post->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'slug', 'content', 'excerpt',
                'author', 'category', 'tags', 'created_at',
            ],
        ]);
    }

    /** @test */
    public function guest_cannot_view_draft_post(): void
    {
        $post = Post::factory()->create(['status' => 'draft']);

        $response = $this->apiGet("/api/public/posts/{$post->id}");

        $this->assertNotFound($response);
    }

    /** @test */
    public function author_can_create_post_with_valid_data(): void
    {
        $category = Category::factory()->create();
        
        $postData = [
            'title' => 'Test Post',
            'content' => 'This is test content for the post.',
            'excerpt' => 'Test excerpt',
            'category_id' => $category->id,
            'status' => 'draft',
            'post_type' => 'post',
        ];

        $response = $this->actingAsAuthor()
            ->apiPost('/api/posts', $postData);

        $this->assertApiSuccess($response, 201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'slug', 'content', 'author',
            ],
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'slug' => 'test-post',
            'author_id' => $this->authorUser->id,
        ]);
    }

    /** @test */
    public function author_cannot_create_post_with_invalid_data(): void
    {
        $postData = [
            'title' => '', // Required field empty
            'content' => '', // Required field empty
            'status' => 'invalid_status',
        ];

        $response = $this->actingAsAuthor()
            ->apiPost('/api/posts', $postData);

        $this->assertValidationError($response, ['title', 'content', 'status']);
    }

    /** @test */
    public function author_can_update_own_post(): void
    {
        $post = Post::factory()->create(['author_id' => $this->authorUser->id]);

        $updateData = [
            'title' => 'Updated Post Title',
            'content' => 'Updated content',
            'status' => 'published',
        ];

        $response = $this->actingAsAuthor()
            ->apiPut("/api/posts/{$post->id}", $updateData);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Post Title',
        ]);
    }

    /** @test */
    public function author_cannot_update_others_post(): void
    {
        $post = Post::factory()->create(['author_id' => $this->editorUser->id]);

        $updateData = [
            'title' => 'Updated Post Title',
            'content' => 'Updated content',
        ];

        $response = $this->actingAsAuthor()
            ->apiPut("/api/posts/{$post->id}", $updateData);

        $this->assertForbidden($response);
    }

    /** @test */
    public function editor_can_update_any_post(): void
    {
        $post = Post::factory()->create(['author_id' => $this->authorUser->id]);

        $updateData = [
            'title' => 'Editor Updated Title',
            'content' => 'Editor updated content',
        ];

        $response = $this->actingAsEditor()
            ->apiPut("/api/posts/{$post->id}", $updateData);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Editor Updated Title',
        ]);
    }

    /** @test */
    public function admin_can_delete_any_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->actingAsAdmin()
            ->apiDelete("/api/posts/{$post->id}");

        $this->assertApiSuccess($response, 200);
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    /** @test */
    public function author_cannot_delete_others_post(): void
    {
        $post = Post::factory()->create(['author_id' => $this->editorUser->id]);

        $response = $this->actingAsAuthor()
            ->apiDelete("/api/posts/{$post->id}");

        $this->assertForbidden($response);
    }

    /** @test */
    public function posts_can_be_filtered_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        Post::factory()->count(2)->create([
            'category_id' => $category1->id,
            'status' => 'published',
        ]);
        Post::factory()->count(3)->create([
            'category_id' => $category2->id,
            'status' => 'published',
        ]);

        $response = $this->apiGet("/api/public/posts?category_id={$category1->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonCount(2, 'data.data');
    }

    /** @test */
    public function posts_can_be_searched(): void
    {
        Post::factory()->create([
            'title' => 'Laravel Tutorial',
            'content' => 'Learn Laravel framework',
            'status' => 'published',
        ]);
        Post::factory()->create([
            'title' => 'PHP Basics',
            'content' => 'Learn PHP programming',
            'status' => 'published',
        ]);

        $response = $this->apiGet('/api/public/posts?search=Laravel');

        $this->assertApiSuccess($response);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.title', 'Laravel Tutorial');
    }

    /** @test */
    public function posts_can_be_sorted(): void
    {
        $oldPost = Post::factory()->create([
            'title' => 'Old Post',
            'status' => 'published',
            'created_at' => now()->subDays(5),
        ]);
        $newPost = Post::factory()->create([
            'title' => 'New Post',
            'status' => 'published',
            'created_at' => now(),
        ]);

        // Sort by created_at ascending
        $response = $this->apiGet('/api/public/posts?sort_by=created_at&sort_order=asc');

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.data.0.title', 'Old Post');
        $response->assertJsonPath('data.data.1.title', 'New Post');
    }

    /** @test */
    public function post_slug_is_auto_generated(): void
    {
        $postData = [
            'title' => 'Test Post Title',
            'content' => 'Test content',
            'status' => 'draft',
        ];

        $response = $this->actingAsAuthor()
            ->apiPost('/api/posts', $postData);

        $this->assertApiSuccess($response, 201);
        $response->assertJsonPath('data.slug', 'test-post-title');
    }

    /** @test */
    public function duplicate_slug_is_handled_automatically(): void
    {
        // Create first post
        Post::factory()->create(['slug' => 'test-post']);

        $postData = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'draft',
        ];

        $response = $this->actingAsAuthor()
            ->apiPost('/api/posts', $postData);

        $this->assertApiSuccess($response, 201);
        // Should get unique slug
        $this->assertStringContainsString('test-post', $response->json('data.slug'));
        $this->assertNotEquals('test-post', $response->json('data.slug'));
    }

    /** @test */
    public function post_can_be_published(): void
    {
        $post = Post::factory()->create([
            'author_id' => $this->authorUser->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAsAuthor()
            ->apiPost("/api/posts/{$post->id}/publish");

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'published',
        ]);
    }

    /** @test */
    public function post_can_be_duplicated(): void
    {
        $post = Post::factory()->create([
            'author_id' => $this->authorUser->id,
            'title' => 'Original Post',
        ]);

        $response = $this->actingAsAuthor()
            ->apiPost("/api/posts/{$post->id}/duplicate");

        $this->assertApiSuccess($response, 201);
        $response->assertJsonPath('data.title', 'Copy of Original Post');
        $response->assertJsonPath('data.status', 'draft');
    }

    /** @test */
    public function post_view_count_is_incremented(): void
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'view_count' => 5,
        ]);

        $response = $this->apiGet("/api/public/posts/{$post->id}");

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'view_count' => 6,
        ]);
    }
}
