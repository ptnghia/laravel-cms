<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FormRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles first
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create a simple user for testing
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'status' => 'active',
        ]);

        // Assign author role to user
        $this->user->assignRole('author');
    }

    /** @test */
    public function post_form_request_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/posts', [
            'title' => '', // Required field empty
            'content' => '', // Required field empty
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'content', 'status', 'post_type']);
    }

    /** @test */
    public function post_form_request_validates_with_valid_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'This is test content',
            'status' => 'draft',
            'post_type' => 'post',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'title', 'slug', 'content',
            ],
        ]);
    }

    /** @test */
    public function post_form_request_auto_generates_slug(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post Title',
            'content' => 'Test content',
            'status' => 'draft',
            'post_type' => 'post',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.slug', 'test-post-title');
    }

    /** @test */
    public function category_form_request_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/categories', [
            'name' => '', // Required field empty
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function category_form_request_validates_with_valid_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/categories', [
            'name' => 'Test Category',
            'description' => 'Test description',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Test Category');
        $response->assertJsonPath('data.slug', 'test-category');
    }

    /** @test */
    public function tag_form_request_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/tags', [
            'name' => '', // Required field empty
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function tag_form_request_validates_with_valid_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/tags', [
            'name' => 'Test Tag',
            'description' => 'Test description',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Test Tag');
        $response->assertJsonPath('data.slug', 'test-tag');
    }

    /** @test */
    public function form_request_returns_vietnamese_error_messages(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/posts', [
            'title' => '',
            'content' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'title' => ['Tiêu đề bài viết là bắt buộc.'],
                'content' => ['Nội dung bài viết là bắt buộc.'],
                'status' => ['Trạng thái bài viết là bắt buộc.'],
                'post_type' => ['Loại bài viết là bắt buộc.'],
            ],
        ]);
    }

    /** @test */
    public function form_request_validates_unique_constraints(): void
    {
        Sanctum::actingAs($this->user);

        // Create first post
        $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'status' => 'draft',
            'post_type' => 'post',
        ]);

        // Try to create second post with same slug
        $response = $this->postJson('/api/posts', [
            'title' => 'Another Post',
            'slug' => 'test-post', // Duplicate slug
            'content' => 'Another content',
            'status' => 'draft',
            'post_type' => 'post',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function form_request_validates_file_uploads(): void
    {
        Sanctum::actingAs($this->user);

        // Test invalid file type for category image
        $response = $this->postJson('/api/categories', [
            'name' => 'Test Category',
            'image' => 'not-an-image-file',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function form_request_validates_seo_meta_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'draft',
            'post_type' => 'post',
            'seo_meta' => [
                'title' => str_repeat('a', 70), // Too long (max 60)
                'description' => str_repeat('b', 170), // Too long (max 160)
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['seo_meta.title', 'seo_meta.description']);
    }

    /** @test */
    public function form_request_prevents_circular_parent_relationships(): void
    {
        Sanctum::actingAs($this->user);

        // Create parent category
        $parent = Category::factory()->create();

        // Try to update parent to have itself as parent
        $response = $this->putJson("/api/categories/{$parent->id}", [
            'name' => 'Updated Category',
            'parent_id' => $parent->id, // Circular reference
        ]);

        // Should succeed but parent_id should be null
        $response->assertStatus(200);
        $this->assertDatabaseHas('categories', [
            'id' => $parent->id,
            'parent_id' => null,
        ]);
    }

    /** @test */
    public function form_request_validates_color_format(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/tags', [
            'name' => 'Test Tag',
            'color' => 'invalid-color', // Invalid hex color
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['color']);
    }

    /** @test */
    public function form_request_sets_default_values(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/categories', [
            'name' => 'Test Category',
            // No sort_order or is_active provided
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.sort_order', 0);
        $response->assertJsonPath('data.is_active', true);
    }
}
