<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(rand(2, 4), true);
        $slug = \Str::slug($name);
        $price = fake()->randomFloat(2, 10, 1000);
        $salePrice = fake()->optional(0.3)->randomFloat(2, $price * 0.5, $price * 0.9);

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => fake()->paragraph(2),
            'content' => fake()->paragraphs(rand(3, 8), true),
            'images' => fake()->optional(0.8)->randomElements([
                'products/product1.jpg',
                'products/product2.jpg',
                'products/product3.jpg',
                'products/product4.jpg',
            ], rand(1, 4)),
            'price' => $price,
            'sale_price' => $salePrice,
            'sku' => fake()->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'stock_status' => fake()->randomElement(['in_stock', 'out_of_stock', 'on_backorder']),
            'attributes' => fake()->optional(0.6)->randomElement([
                [
                    'color' => fake()->colorName(),
                    'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
                    'material' => fake()->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk']),
                ],
                [
                    'brand' => fake()->company(),
                    'model' => fake()->bothify('Model-###'),
                    'warranty' => fake()->randomElement(['1 year', '2 years', '3 years']),
                ],
                null,
            ]),
            'variations' => fake()->optional(0.4)->randomElement([
                [
                    ['name' => 'Small', 'price' => $price, 'sku' => fake()->regexify('[A-Z]{3}[0-9]{6}')],
                    ['name' => 'Medium', 'price' => $price + 10, 'sku' => fake()->regexify('[A-Z]{3}[0-9]{6}')],
                    ['name' => 'Large', 'price' => $price + 20, 'sku' => fake()->regexify('[A-Z]{3}[0-9]{6}')],
                ],
                null,
            ]),
            'category_id' => \App\Models\Category::factory(),
            'rating_avg' => fake()->optional(0.7)->randomFloat(1, 1, 5),
            'rating_count' => fake()->numberBetween(0, 100),
            'is_active' => fake()->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Create an active product.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'stock_status' => 'in_stock',
            'stock_quantity' => fake()->numberBetween(10, 100),
        ]);
    }

    /**
     * Create an out of stock product.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_status' => 'out_of_stock',
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Create a product on sale.
     */
    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? fake()->randomFloat(2, 50, 500);
            return [
                'sale_price' => fake()->randomFloat(2, $price * 0.5, $price * 0.8),
            ];
        });
    }

    /**
     * Create a popular product.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating_avg' => fake()->randomFloat(1, 4, 5),
            'rating_count' => fake()->numberBetween(50, 500),
        ]);
    }
}
