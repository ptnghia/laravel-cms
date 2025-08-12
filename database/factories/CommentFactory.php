<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'commentable_id' => Post::factory(),
            'commentable_type' => Post::class,
            'user_id' => fake()->optional(0.7)->randomElement([User::factory(), null]),
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'author_url' => fake()->optional(0.3)->url(),
            'content' => fake()->paragraph(rand(1, 4)),
            'parent_id' => null, // Will be set manually for replies
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'status' => fake()->randomElement(['pending', 'approved', 'spam', 'rejected']),
            'metadata' => fake()->optional(0.3)->randomElement([
                [
                    'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                    'platform' => fake()->randomElement(['Windows', 'Mac', 'Linux', 'Mobile']),
                ],
                null,
            ]),
        ];
    }

    /**
     * Create an approved comment.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Create a pending comment.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Create a spam comment.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'spam',
            'content' => 'This is spam content with suspicious links',
        ]);
    }

    /**
     * Create a comment from a logged-in user.
     */
    public function fromUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
            'author_name' => null, // Will be filled from user
            'author_email' => null, // Will be filled from user
        ]);
    }

    /**
     * Create a guest comment.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
        ]);
    }

    /**
     * Create a reply comment.
     */
    public function reply(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => \App\Models\Comment::factory(),
        ]);
    }

    /**
     * Create a comment for a specific post.
     */
    public function forPost($postId): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_id' => $postId,
            'commentable_type' => Post::class,
        ]);
    }

    /**
     * Create a comment for a specific page.
     */
    public function forPage($pageId): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_id' => $pageId,
            'commentable_type' => \App\Models\Page::class,
        ]);
    }
}
