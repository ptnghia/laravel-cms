<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $permissions = [
            // Users module
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'Can view users list', 'module' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Can create new users', 'module' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'description' => 'Can edit users', 'module' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Can delete users', 'module' => 'users'],

            // Posts module
            ['name' => 'posts.view', 'display_name' => 'View Posts', 'description' => 'Can view posts', 'module' => 'posts'],
            ['name' => 'posts.create', 'display_name' => 'Create Posts', 'description' => 'Can create posts', 'module' => 'posts'],
            ['name' => 'posts.edit', 'display_name' => 'Edit Posts', 'description' => 'Can edit posts', 'module' => 'posts'],
            ['name' => 'posts.delete', 'display_name' => 'Delete Posts', 'description' => 'Can delete posts', 'module' => 'posts'],
            ['name' => 'posts.publish', 'display_name' => 'Publish Posts', 'description' => 'Can publish posts', 'module' => 'posts'],

            // Pages module
            ['name' => 'pages.view', 'display_name' => 'View Pages', 'description' => 'Can view pages', 'module' => 'pages'],
            ['name' => 'pages.create', 'display_name' => 'Create Pages', 'description' => 'Can create pages', 'module' => 'pages'],
            ['name' => 'pages.edit', 'display_name' => 'Edit Pages', 'description' => 'Can edit pages', 'module' => 'pages'],
            ['name' => 'pages.delete', 'display_name' => 'Delete Pages', 'description' => 'Can delete pages', 'module' => 'pages'],

            // Media module
            ['name' => 'media.view', 'display_name' => 'View Media', 'description' => 'Can view media files', 'module' => 'media'],
            ['name' => 'media.upload', 'display_name' => 'Upload Media', 'description' => 'Can upload media files', 'module' => 'media'],
            ['name' => 'media.delete', 'display_name' => 'Delete Media', 'description' => 'Can delete media files', 'module' => 'media'],

            // Settings module
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'description' => 'Can view settings', 'module' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Edit Settings', 'description' => 'Can edit settings', 'module' => 'settings'],

            // Comments module
            ['name' => 'comments.view', 'display_name' => 'View Comments', 'description' => 'Can view comments', 'module' => 'comments'],
            ['name' => 'comments.moderate', 'display_name' => 'Moderate Comments', 'description' => 'Can moderate comments', 'module' => 'comments'],
            ['name' => 'comments.delete', 'display_name' => 'Delete Comments', 'description' => 'Can delete comments', 'module' => 'comments'],
        ];

        $permission = fake()->randomElement($permissions);

        return [
            'name' => $permission['name'],
            'display_name' => $permission['display_name'],
            'description' => $permission['description'],
            'module' => $permission['module'],
        ];
    }

    /**
     * Create permissions for a specific module.
     */
    public function forModule(string $module): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => $module,
        ]);
    }
}
