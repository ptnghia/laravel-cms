<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends BaseApiTest
{
    /** @test */
    public function user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->apiPost('/api/auth/register', $userData);

        $this->assertApiSuccess($response, 201);
        $response->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }

    /** @test */
    public function user_cannot_register_with_invalid_data(): void
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
        ];

        $response = $this->apiPost('/api/auth/register', $userData);

        $this->assertValidationError($response, ['name', 'email', 'password']);
    }

    /** @test */
    public function user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->apiPost('/api/auth/login', $loginData);

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->apiPost('/api/auth/login', $loginData);

        $this->assertApiError($response, 401);
    }

    /** @test */
    public function authenticated_user_can_logout(): void
    {
        $response = $this->actingAsUser()
            ->apiPost('/api/auth/logout');

        $this->assertApiSuccess($response);
        $response->assertJson(['message' => 'Logged out successfully']);
    }

    /** @test */
    public function authenticated_user_can_refresh_token(): void
    {
        $response = $this->actingAsUser()
            ->apiPost('/api/auth/refresh');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ]);
    }

    /** @test */
    public function user_can_request_password_reset(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->apiPost('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $this->assertApiSuccess($response);
        $response->assertJson(['message' => 'Password reset link sent']);
    }

    /** @test */
    public function user_cannot_request_password_reset_for_nonexistent_email(): void
    {
        $response = $this->apiPost('/api/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $this->assertValidationError($response, ['email']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->apiGet('/api/profile');
        $this->assertUnauthorized($response);

        $response = $this->apiPost('/api/posts');
        $this->assertUnauthorized($response);

        $response = $this->apiGet('/api/admin/users');
        $this->assertUnauthorized($response);
    }

    /** @test */
    public function user_with_insufficient_role_cannot_access_admin_routes(): void
    {
        $response = $this->actingAsUser()
            ->apiGet('/api/admin/users');

        $this->assertForbidden($response);
    }

    /** @test */
    public function admin_user_can_access_admin_routes(): void
    {
        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/users');

        $this->assertApiSuccess($response);
    }

    /** @test */
    public function api_returns_consistent_error_format(): void
    {
        $response = $this->apiPost('/api/auth/login', [
            'email' => 'invalid',
            'password' => '',
        ]);

        $this->assertValidationError($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'timestamp',
            'status_code',
            'errors',
        ]);
    }

    /** @test */
    public function api_includes_proper_headers(): void
    {
        $response = $this->actingAsUser()
            ->apiGet('/api/profile');

        $response->assertHeader('X-API-Version');
        $response->assertHeader('X-Response-Time');
    }
}
