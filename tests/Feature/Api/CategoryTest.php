<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Post;

class CategoryTest extends BaseApiTest
{
    /** @test */
    public function guest_can_view_active_categories(): void
    {
        Category::factory()->count(3)->create(['is_active' => true]);
        Category::factory()->count(2)->create(['is_active' => false]);

        $response = $this->apiGet('/api/public/categories');

        $this->assertApiSuccess($response);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function guest_can_view_single_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->apiGet("/api/public/categories/{$category->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'slug', 'description',
                'posts_count', 'level', 'breadcrumb',
            ],
        ]);
    }

    /** @test */
    public function guest_can_view_category_by_slug(): void
    {
        $category = Category::factory()->create([
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $response = $this->apiGet('/api/public/categories/slug/test-category');

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.slug', 'test-category');
    }

    /** @test */
    public function guest_can_view_category_tree(): void
    {
        $parent = Category::factory()->create(['is_active' => true]);
        $child1 = Category::factory()->create([
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);
        $child2 = Category::factory()->create([
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $response = $this->apiGet('/api/public/categories/tree');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'children',
                ],
            ],
        ]);
    }

    /** @test */
    public function editor_can_create_category_with_valid_data(): void
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'This is a test category',
            'is_active' => true,
        ];

        $response = $this->actingAsEditor()
            ->apiPost('/api/categories', $categoryData);

        $this->assertApiSuccess($response, 201);
        $response->assertJsonPath('data.name', 'Test Category');
        $response->assertJsonPath('data.slug', 'test-category');

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    /** @test */
    public function editor_cannot_create_category_with_invalid_data(): void
    {
        $categoryData = [
            'name' => '', // Required field empty
            'parent_id' => 999, // Non-existent parent
        ];

        $response = $this->actingAsEditor()
            ->apiPost('/api/categories', $categoryData);

        $this->assertValidationError($response, ['name', 'parent_id']);
    }

    /** @test */
    public function regular_user_cannot_create_category(): void
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test description',
        ];

        $response = $this->actingAsUser()
            ->apiPost('/api/categories', $categoryData);

        $this->assertForbidden($response);
    }

    /** @test */
    public function editor_can_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Original Name']);

        $updateData = [
            'name' => 'Updated Category Name',
            'description' => 'Updated description',
        ];

        $response = $this->actingAsEditor()
            ->apiPut("/api/categories/{$category->id}", $updateData);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
        ]);
    }

    /** @test */
    public function admin_can_delete_empty_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAsAdmin()
            ->apiDelete("/api/categories/{$category->id}");

        $this->assertApiSuccess($response);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    /** @test */
    public function admin_cannot_delete_category_with_posts(): void
    {
        $category = Category::factory()->create();
        Post::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAsAdmin()
            ->apiDelete("/api/categories/{$category->id}");

        $this->assertApiError($response, 400);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @test */
    public function categories_can_be_filtered_by_parent(): void
    {
        $parent = Category::factory()->create();
        $children = Category::factory()->count(3)->create(['parent_id' => $parent->id]);
        Category::factory()->count(2)->create(); // Root categories

        $response = $this->apiGet("/api/public/categories?parent_id={$parent->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function categories_can_be_searched(): void
    {
        Category::factory()->create([
            'name' => 'Laravel Development',
            'is_active' => true,
        ]);
        Category::factory()->create([
            'name' => 'PHP Programming',
            'is_active' => true,
        ]);

        $response = $this->apiGet('/api/public/categories?search=Laravel');

        $this->assertApiSuccess($response);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Laravel Development');
    }

    /** @test */
    public function category_slug_is_auto_generated(): void
    {
        $categoryData = [
            'name' => 'Test Category Name',
            'description' => 'Test description',
        ];

        $response = $this->actingAsEditor()
            ->apiPost('/api/categories', $categoryData);

        $this->assertApiSuccess($response, 201);
        $response->assertJsonPath('data.slug', 'test-category-name');
    }

    /** @test */
    public function category_hierarchy_is_calculated_correctly(): void
    {
        $parent = Category::factory()->create(['name' => 'Parent']);
        $child = Category::factory()->create([
            'name' => 'Child',
            'parent_id' => $parent->id,
        ]);
        $grandchild = Category::factory()->create([
            'name' => 'Grandchild',
            'parent_id' => $child->id,
        ]);

        $response = $this->apiGet("/api/public/categories/{$grandchild->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.level', 2);
        $response->assertJsonCount(3, 'data.breadcrumb');
    }

    /** @test */
    public function category_posts_count_is_accurate(): void
    {
        $category = Category::factory()->create();
        Post::factory()->count(5)->create([
            'category_id' => $category->id,
            'status' => 'published',
        ]);
        Post::factory()->count(2)->create([
            'category_id' => $category->id,
            'status' => 'draft',
        ]);

        $response = $this->apiGet("/api/public/categories/{$category->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.posts_count', 5); // Only published posts
    }
}
