<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Admin', 'description' => 'Full system access'],
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Administrative access'],
            ['name' => 'editor', 'display_name' => 'Editor', 'description' => 'Content editing access'],
            ['name' => 'author', 'display_name' => 'Author', 'description' => 'Content creation access'],
            ['name' => 'user', 'display_name' => 'User', 'description' => 'Basic user access'],
        ];

        $role = fake()->randomElement($roles);

        return [
            'name' => $role['name'],
            'display_name' => $role['display_name'],
            'description' => $role['description'],
        ];
    }

    /**
     * Create a super admin role.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'description' => 'Full system access with all permissions',
        ]);
    }

    /**
     * Create an admin role.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Administrative access to manage content and users',
        ]);
    }

    /**
     * Create an editor role.
     */
    public function editor(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'editor',
            'display_name' => 'Editor',
            'description' => 'Can edit and publish content',
        ]);
    }

    /**
     * Create an author role.
     */
    public function author(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'author',
            'display_name' => 'Author',
            'description' => 'Can create and edit own content',
        ]);
    }

    /**
     * Create a user role.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'user',
            'display_name' => 'User',
            'description' => 'Basic user with limited access',
        ]);
    }
}
