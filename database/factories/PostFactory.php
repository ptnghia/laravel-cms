<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(rand(4, 8));
        $slug = Str::slug($title);
        $content = fake()->paragraphs(rand(5, 15), true);
        $excerpt = fake()->optional(0.7)->paragraph(2);

        return [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'featured_image_id' => null, // Will be set manually if needed
            'gallery' => fake()->optional(0.3)->randomElements([
                'images/gallery1.jpg',
                'images/gallery2.jpg',
                'images/gallery3.jpg',
            ], rand(1, 3)),
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'status' => fake()->randomElement(['draft', 'published', 'scheduled']),
            'post_type' => fake()->randomElement(['post', 'article', 'news']),
            'published_at' => fake()->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'scheduled_at' => fake()->optional(0.1)->dateTimeBetween('now', '+1 month'),
            'view_count' => fake()->numberBetween(0, 1000),
            'comment_count' => fake()->numberBetween(0, 50),
            'rating_avg' => fake()->randomFloat(1, 0, 5),
            'rating_count' => fake()->numberBetween(0, 20),
            'seo_meta' => fake()->optional(0.5)->randomElement([
                [
                    'title' => fake()->sentence(6),
                    'description' => fake()->paragraph(1),
                    'keywords' => fake()->words(5, true),
                ],
                null,
            ]),
            'meta_data' => fake()->optional(0.3)->randomElement([
                [
                    'reading_time' => fake()->numberBetween(2, 15),
                    'featured' => fake()->boolean(),
                    'sticky' => fake()->boolean(10),
                ],
                null,
            ]),
        ];
    }

    /**
     * Create a published post.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Create a draft post.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Create a scheduled post.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'scheduled_at' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }

    /**
     * Create a popular post.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => fake()->numberBetween(500, 5000),
            'comment_count' => fake()->numberBetween(10, 100),
            'rating_avg' => fake()->randomFloat(1, 4, 5),
            'rating_count' => fake()->numberBetween(20, 200),
        ]);
    }

    /**
     * Create a featured post.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_data' => [
                'featured' => true,
                'sticky' => fake()->boolean(30),
                'reading_time' => fake()->numberBetween(5, 20),
            ],
        ]);
    }

    /**
     * Create a post with SEO meta.
     */
    public function withSeo(): static
    {
        return $this->state(fn (array $attributes) => [
            'seo_meta' => [
                'title' => fake()->sentence(6),
                'description' => fake()->paragraph(1),
                'keywords' => fake()->words(5, true),
            ],
        ]);
    }
}
