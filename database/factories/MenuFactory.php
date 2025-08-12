<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $menuNames = [
            'Main Menu', 'Header Menu', 'Footer Menu', 'Sidebar Menu',
            'Primary Navigation', 'Secondary Navigation', 'Mobile Menu',
            'Admin Menu', 'User Menu', 'Category Menu'
        ];

        $locations = [
            'header', 'footer', 'sidebar', 'mobile', 'admin',
            'primary', 'secondary', 'user-menu', 'category-menu'
        ];

        return [
            'name' => fake()->randomElement($menuNames),
            'location' => fake()->randomElement($locations),
            'menu_items' => fake()->optional(0.3)->randomElement([
                [
                    'cache_enabled' => true,
                    'cache_duration' => 3600,
                    'css_class' => fake()->word(),
                ],
                null,
            ]),
            'is_active' => fake()->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Create an active menu.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create a menu for specific location.
     */
    public function forLocation(string $location): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => $location,
            'name' => ucfirst($location) . ' Menu',
        ]);
    }

    /**
     * Create a header menu.
     */
    public function header(): static
    {
        return $this->forLocation('header');
    }

    /**
     * Create a footer menu.
     */
    public function footer(): static
    {
        return $this->forLocation('footer');
    }

    /**
     * Create a sidebar menu.
     */
    public function sidebar(): static
    {
        return $this->forLocation('sidebar');
    }

    /**
     * Create a mobile menu.
     */
    public function mobile(): static
    {
        return $this->forLocation('mobile');
    }
}
