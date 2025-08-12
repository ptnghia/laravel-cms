<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SimpleFormRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function form_request_validation_works(): void
    {
        // Create roles
        $authorRole = Role::create(['name' => 'author', 'display_name' => 'Author']);
        
        // Create user with role
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach($authorRole->id);
        
        // Authenticate user
        Sanctum::actingAs($user);

        // Test validation error
        $response = $this->postJson('/api/posts', [
            'title' => '', // Required field empty
            'content' => '', // Required field empty
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
        
        // Check Vietnamese error messages
        $errors = $response->json('errors');
        $this->assertStringContainsString('bắt buộc', $errors['title'][0]);
        $this->assertStringContainsString('bắt buộc', $errors['content'][0]);
    }

    /** @test */
    public function form_request_creates_post_successfully(): void
    {
        // Create roles
        $authorRole = Role::create(['name' => 'author', 'display_name' => 'Author']);
        
        // Create user with role
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach($authorRole->id);
        
        // Authenticate user
        Sanctum::actingAs($user);

        // Test successful creation
        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'This is test content',
            'status' => 'draft',
            'post_type' => 'post',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'title', 'slug', 'content'],
        ]);
        
        // Check auto-generated slug
        $this->assertEquals('test-post', $response->json('data.slug'));
    }

    /** @test */
    public function unauthorized_user_cannot_create_post(): void
    {
        // Create user without roles
        $user = User::factory()->create(['status' => 'active']);
        
        // Authenticate user
        Sanctum::actingAs($user);

        // Test authorization failure
        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'This is test content',
            'status' => 'draft',
            'post_type' => 'post',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function category_form_request_works(): void
    {
        // Create roles
        $editorRole = Role::create(['name' => 'editor', 'display_name' => 'Editor']);
        
        // Create user with role
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach($editorRole->id);
        
        // Authenticate user
        Sanctum::actingAs($user);

        // Test validation error
        $response = $this->postJson('/api/categories', [
            'name' => '', // Required field empty
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
        
        // Test successful creation
        $response = $this->postJson('/api/categories', [
            'name' => 'Test Category',
            'description' => 'Test description',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Test Category');
        $response->assertJsonPath('data.slug', 'test-category');
    }

    /** @test */
    public function tag_form_request_works(): void
    {
        // Create roles
        $authorRole = Role::create(['name' => 'author', 'display_name' => 'Author']);
        
        // Create user with role
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach($authorRole->id);
        
        // Authenticate user
        Sanctum::actingAs($user);

        // Test validation error
        $response = $this->postJson('/api/tags', [
            'name' => '', // Required field empty
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
        
        // Test successful creation
        $response = $this->postJson('/api/tags', [
            'name' => 'Test Tag',
            'description' => 'Test description',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Test Tag');
        $response->assertJsonPath('data.slug', 'test-tag');
        
        // Check auto-generated color
        $this->assertNotNull($response->json('data.color'));
        $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $response->json('data.color'));
    }
}
