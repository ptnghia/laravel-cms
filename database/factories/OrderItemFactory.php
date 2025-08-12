<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 10, 200);
        $totalPrice = $quantity * $unitPrice;

        return [
            'order_id' => \App\Models\Order::factory(),
            'product_id' => \App\Models\Product::factory(),
            'product_name' => fake()->words(3, true),
            'product_sku' => fake()->regexify('[A-Z]{3}[0-9]{6}'),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'product_options' => fake()->optional(0.4)->randomElement([
                [
                    'color' => fake()->colorName(),
                    'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
                ],
                [
                    'variant' => fake()->word(),
                    'custom_text' => fake()->optional(0.3)->sentence(),
                ],
                null,
            ]),
        ];
    }

    /**
     * Create an order item for specific order.
     */
    public function forOrder($orderId): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $orderId,
        ]);
    }

    /**
     * Create an order item for specific product.
     */
    public function forProduct($productId): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $productId,
        ]);
    }

    /**
     * Create an order item with specific quantity.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            $unitPrice = $attributes['unit_price'] ?? fake()->randomFloat(2, 10, 200);
            return [
                'quantity' => $quantity,
                'total_price' => $quantity * $unitPrice,
            ];
        });
    }
}
