<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();
        $slug = Str::slug($name);

        return [
            'name' => $name,
            'slug' => $slug,
            'color' => fake()->optional(0.6)->hexColor(),
            'usage_count' => fake()->numberBetween(0, 50),
        ];
    }

    /**
     * Create a popular tag.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => fake()->numberBetween(20, 100),
        ]);
    }

    /**
     * Create a tag with specific color.
     */
    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => $color,
        ]);
    }

    /**
     * Create an unused tag.
     */
    public function unused(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => 0,
        ]);
    }
}
