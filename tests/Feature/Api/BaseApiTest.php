<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

abstract class BaseApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $adminUser;
    protected User $editorUser;
    protected User $authorUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createRoles();
        $this->createTestUsers();
    }

    /**
     * Create test roles.
     */
    protected function createRoles(): void
    {
        Role::create(['name' => 'super_admin', 'display_name' => 'Super Admin']);
        Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        Role::create(['name' => 'editor', 'display_name' => 'Editor']);
        Role::create(['name' => 'author', 'display_name' => 'Author']);
        Role::create(['name' => 'user', 'display_name' => 'User']);
    }

    /**
     * Create test users with different roles.
     */
    protected function createTestUsers(): void
    {
        // Admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'status' => 'active',
        ]);
        $this->adminUser->assignRole('admin');

        // Editor user
        $this->editorUser = User::factory()->create([
            'email' => 'editor@test.com',
            'status' => 'active',
        ]);
        $this->editorUser->assignRole('editor');

        // Author user
        $this->authorUser = User::factory()->create([
            'email' => 'author@test.com',
            'status' => 'active',
        ]);
        $this->authorUser->assignRole('author');

        // Regular user
        $this->regularUser = User::factory()->create([
            'email' => 'user@test.com',
            'status' => 'active',
        ]);
        $this->regularUser->assignRole('user');
    }

    /**
     * Authenticate as admin user.
     */
    protected function actingAsAdmin(): self
    {
        Sanctum::actingAs($this->adminUser);
        return $this;
    }

    /**
     * Authenticate as editor user.
     */
    protected function actingAsEditor(): self
    {
        Sanctum::actingAs($this->editorUser);
        return $this;
    }

    /**
     * Authenticate as author user.
     */
    protected function actingAsAuthor(): self
    {
        Sanctum::actingAs($this->authorUser);
        return $this;
    }

    /**
     * Authenticate as regular user.
     */
    protected function actingAsUser(): self
    {
        Sanctum::actingAs($this->regularUser);
        return $this;
    }

    /**
     * Assert API response structure.
     */
    protected function assertApiResponse($response, int $status = 200): void
    {
        $response->assertStatus($status);
        $response->assertJsonStructure([
            'success',
            'message',
            'timestamp',
            'status_code',
        ]);
    }

    /**
     * Assert API success response.
     */
    protected function assertApiSuccess($response, int $status = 200): void
    {
        $this->assertApiResponse($response, $status);
        $response->assertJson(['success' => true]);
    }

    /**
     * Assert API error response.
     */
    protected function assertApiError($response, int $status = 400): void
    {
        $this->assertApiResponse($response, $status);
        $response->assertJson(['success' => false]);
    }

    /**
     * Assert validation error response.
     */
    protected function assertValidationError($response, array $fields = []): void
    {
        $this->assertApiError($response, 422);
        $response->assertJsonStructure(['errors']);
        
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $response->assertJsonValidationErrors($field);
            }
        }
    }

    /**
     * Assert unauthorized response.
     */
    protected function assertUnauthorized($response): void
    {
        $this->assertApiError($response, 401);
    }

    /**
     * Assert forbidden response.
     */
    protected function assertForbidden($response): void
    {
        $this->assertApiError($response, 403);
    }

    /**
     * Assert not found response.
     */
    protected function assertNotFound($response): void
    {
        $this->assertApiError($response, 404);
    }

    /**
     * Assert paginated response structure.
     */
    protected function assertPaginatedResponse($response): void
    {
        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'data',
                'current_page',
                'last_page',
                'per_page',
                'total',
                'from',
                'to',
            ],
        ]);
    }

    /**
     * Get API headers.
     */
    protected function getApiHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Make authenticated API request.
     */
    protected function apiRequest(string $method, string $uri, array $data = [], User $user = null): \Illuminate\Testing\TestResponse
    {
        if ($user) {
            Sanctum::actingAs($user);
        }

        return $this->json($method, $uri, $data, $this->getApiHeaders());
    }

    /**
     * Make GET API request.
     */
    protected function apiGet(string $uri, User $user = null): \Illuminate\Testing\TestResponse
    {
        return $this->apiRequest('GET', $uri, [], $user);
    }

    /**
     * Make POST API request.
     */
    protected function apiPost(string $uri, array $data = [], User $user = null): \Illuminate\Testing\TestResponse
    {
        return $this->apiRequest('POST', $uri, $data, $user);
    }

    /**
     * Make PUT API request.
     */
    protected function apiPut(string $uri, array $data = [], User $user = null): \Illuminate\Testing\TestResponse
    {
        return $this->apiRequest('PUT', $uri, $data, $user);
    }

    /**
     * Make DELETE API request.
     */
    protected function apiDelete(string $uri, User $user = null): \Illuminate\Testing\TestResponse
    {
        return $this->apiRequest('DELETE', $uri, [], $user);
    }
}
