<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(rand(1, 3), true);
        $slug = Str::slug($name);

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => fake()->optional(0.7)->paragraph(),
            'image' => fake()->optional(0.3)->imageUrl(400, 300, 'business'),
            'parent_id' => null, // Will be set manually for child categories
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => fake()->boolean(90), // 90% chance of being active
            'seo_meta' => fake()->optional(0.5)->randomElement([
                [
                    'title' => fake()->sentence(6),
                    'description' => fake()->paragraph(1),
                    'keywords' => fake()->words(5, true),
                ],
                null,
            ]),
        ];
    }

    /**
     * Create an active category.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create a root category (no parent).
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Create a child category.
     */
    public function child(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => \App\Models\Category::factory(),
        ]);
    }

    /**
     * Create a category with specific parent.
     */
    public function withParent($parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Create a category with SEO meta.
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
