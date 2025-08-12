<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(rand(2, 6));
        $slug = Str::slug($title);
        $content = fake()->paragraphs(rand(3, 10), true);

        return [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'template' => fake()->randomElement(['default', 'landing', 'contact', 'about', 'services']),
            'page_builder_data' => fake()->optional(0.4)->randomElement([
                [
                    'sections' => [
                        [
                            'type' => 'hero',
                            'title' => fake()->sentence(),
                            'subtitle' => fake()->paragraph(1),
                            'image' => fake()->imageUrl(1200, 600),
                        ],
                        [
                            'type' => 'content',
                            'content' => fake()->paragraphs(3, true),
                        ],
                    ],
                ],
                null,
            ]),
            'status' => fake()->randomElement(['draft', 'published']),
            'published_at' => fake()->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'parent_id' => null, // Will be set manually for child pages
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => fake()->boolean(95), // 95% chance of being active
            'seo_meta' => fake()->optional(0.6)->randomElement([
                [
                    'title' => fake()->sentence(6),
                    'description' => fake()->paragraph(1),
                    'keywords' => fake()->words(5, true),
                ],
                null,
            ]),
            'meta_data' => fake()->optional(0.3)->randomElement([
                [
                    'show_in_menu' => fake()->boolean(),
                    'require_auth' => fake()->boolean(10),
                    'custom_css' => fake()->optional(0.2)->text(100),
                ],
                null,
            ]),
        ];
    }

    /**
     * Create a published page.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'is_active' => true,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Create a draft page.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Create a landing page.
     */
    public function landing(): static
    {
        return $this->state(fn (array $attributes) => [
            'template' => 'landing',
            'page_builder_data' => [
                'sections' => [
                    [
                        'type' => 'hero',
                        'title' => fake()->sentence(),
                        'subtitle' => fake()->paragraph(1),
                        'cta_text' => 'Get Started',
                        'cta_url' => '/contact',
                    ],
                    [
                        'type' => 'features',
                        'title' => 'Our Features',
                        'features' => [
                            ['title' => fake()->words(2, true), 'description' => fake()->sentence()],
                            ['title' => fake()->words(2, true), 'description' => fake()->sentence()],
                            ['title' => fake()->words(2, true), 'description' => fake()->sentence()],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Create a child page.
     */
    public function child(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => \App\Models\Page::factory(),
        ]);
    }
}
