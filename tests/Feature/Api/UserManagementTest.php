<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;

class UserManagementTest extends BaseApiTest
{
    /** @test */
    public function admin_can_view_all_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/users');

        $this->assertApiSuccess($response);
        $this->assertPaginatedResponse($response);
        // Should include test users + factory users
        $this->assertGreaterThanOrEqual(5, count($response->json('data.data')));
    }

    /** @test */
    public function regular_user_cannot_view_users_list(): void
    {
        $response = $this->actingAsUser()
            ->apiGet('/api/admin/users');

        $this->assertForbidden($response);
    }

    /** @test */
    public function admin_can_create_user_with_valid_data(): void
    {
        $userData = [
            'name' => 'New Test User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
            'roles' => ['author'],
        ];

        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/users', $userData);

        $this->assertApiSuccess($response, 201);
        $response->assertJsonPath('data.name', 'New Test User');
        $response->assertJsonPath('data.email', 'newuser@test.com');

        $this->assertDatabaseHas('users', [
            'name' => 'New Test User',
            'email' => 'newuser@test.com',
        ]);
    }

    /** @test */
    public function admin_cannot_create_user_with_invalid_data(): void
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
        ];

        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/users', $userData);

        $this->assertValidationError($response, ['name', 'email', 'password']);
    }

    /** @test */
    public function admin_can_update_user(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email, // Keep same email
        ];

        $response = $this->actingAsAdmin()
            ->apiPut("/api/admin/users/{$user->id}", $updateData);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function admin_can_assign_roles_to_user(): void
    {
        $user = User::factory()->create();
        $editorRole = Role::where('name', 'editor')->first();

        $response = $this->actingAsAdmin()
            ->apiPost("/api/admin/users/{$user->id}/assign-roles", [
                'roles' => ['editor'],
            ]);

        $this->assertApiSuccess($response);
        $this->assertTrue($user->fresh()->hasRole('editor'));
    }

    /** @test */
    public function admin_can_suspend_user(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAsAdmin()
            ->apiPost("/api/admin/users/{$user->id}/suspend");

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'suspended',
        ]);
    }

    /** @test */
    public function admin_can_activate_user(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);

        $response = $this->actingAsAdmin()
            ->apiPost("/api/admin/users/{$user->id}/activate");

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_view_user_statistics(): void
    {
        User::factory()->count(10)->create(['status' => 'active']);
        User::factory()->count(3)->create(['status' => 'suspended']);

        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/users/statistics');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'statistics' => [
                    'total_users',
                    'active_users',
                    'suspended_users',
                    'users_by_role',
                    'recent_registrations',
                ],
            ],
        ]);
    }

    /** @test */
    public function users_can_be_filtered_by_status(): void
    {
        User::factory()->count(5)->create(['status' => 'active']);
        User::factory()->count(3)->create(['status' => 'suspended']);

        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/users?status=active');

        $this->assertApiSuccess($response);
        // Should include test users + factory users with active status
        $this->assertGreaterThanOrEqual(5, count($response->json('data.data')));
    }

    /** @test */
    public function users_can_be_searched(): void
    {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/users?search=John');

        $this->assertApiSuccess($response);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.name', 'John Doe');
    }

    /** @test */
    public function users_can_be_filtered_by_role(): void
    {
        $editorRole = Role::where('name', 'editor')->first();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $user1->assignRole('editor');
        $user2->assignRole('author');

        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/users?role=editor');

        $this->assertApiSuccess($response);
        // Should include editor test user + new editor user
        $this->assertGreaterThanOrEqual(2, count($response->json('data.data')));
    }

    /** @test */
    public function admin_cannot_delete_super_admin(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $response = $this->actingAsAdmin()
            ->apiDelete("/api/admin/users/{$superAdmin->id}");

        $this->assertForbidden($response);
    }

    /** @test */
    public function admin_can_delete_regular_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsAdmin()
            ->apiDelete("/api/admin/users/{$user->id}");

        $this->assertApiSuccess($response);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function user_email_must_be_unique(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@test.com']);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@test.com', // Duplicate email
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/users', $userData);

        $this->assertValidationError($response, ['email']);
    }

    /** @test */
    public function user_password_must_meet_requirements(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123', // Too short
            'password_confirmation' => '123',
        ];

        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/users', $userData);

        $this->assertValidationError($response, ['password']);
    }

    /** @test */
    public function user_roles_are_validated(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['nonexistent_role'],
        ];

        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/users', $userData);

        $this->assertValidationError($response, ['roles.0']);
    }

    /** @test */
    public function user_profile_includes_computed_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('author');

        $response = $this->actingAsAdmin()
            ->apiGet("/api/admin/users/{$user->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'email', 'status',
                'roles', 'permissions', 'avatar_url',
                'can_edit', 'can_delete',
                'created_at', 'updated_at',
            ],
        ]);
    }
}
