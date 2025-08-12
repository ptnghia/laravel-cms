<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $menuItems = [
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'About', 'url' => '/about'],
            ['title' => 'Services', 'url' => '/services'],
            ['title' => 'Blog', 'url' => '/blog'],
            ['title' => 'Contact', 'url' => '/contact'],
            ['title' => 'Products', 'url' => '/products'],
            ['title' => 'Portfolio', 'url' => '/portfolio'],
            ['title' => 'News', 'url' => '/news'],
            ['title' => 'FAQ', 'url' => '/faq'],
            ['title' => 'Support', 'url' => '/support'],
        ];

        $item = fake()->randomElement($menuItems);

        return [
            'menu_id' => \App\Models\Menu::factory(),
            'title' => $item['title'],
            'url' => $item['url'],
            'parent_id' => null, // Will be set manually for submenus
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Create a root menu item (no parent).
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Create a submenu item.
     */
    public function submenu(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => \App\Models\MenuItem::factory(),
        ]);
    }

    /**
     * Create a menu item with specific parent.
     */
    public function withParent($parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Create a menu item for specific menu.
     */
    public function forMenu($menuId): static
    {
        return $this->state(fn (array $attributes) => [
            'menu_id' => $menuId,
        ]);
    }

    /**
     * Create an external link menu item.
     */
    public function externalLink(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => fake()->company(),
            'url' => fake()->url(),
        ]);
    }

    /**
     * Create a dropdown menu item.
     */
    public function dropdown(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => fake()->words(2, true),
            'url' => '#', // Dropdown items usually don't have direct URLs
        ]);
    }

    /**
     * Create common menu items.
     */
    public function home(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Home',
            'url' => '/',
            'sort_order' => 0,
        ]);
    }

    public function about(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'About',
            'url' => '/about',
            'sort_order' => 10,
        ]);
    }

    public function contact(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Contact',
            'url' => '/contact',
            'sort_order' => 90,
        ]);
    }

    public function blog(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Blog',
            'url' => '/blog',
            'sort_order' => 20,
        ]);
    }
}
