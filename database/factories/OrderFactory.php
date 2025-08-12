<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 500);
        $taxAmount = $subtotal * 0.1; // 10% tax
        $shippingAmount = fake()->randomFloat(2, 5, 25);
        $totalAmount = $subtotal + $taxAmount + $shippingAmount;

        return [
            'order_number' => 'ORD-' . date('Y') . '-' . fake()->unique()->numberBetween(100000, 999999),
            'user_id' => \App\Models\User::factory(),
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'billing_address' => [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'company' => fake()->optional(0.3)->company(),
                'address_1' => fake()->streetAddress(),
                'address_2' => fake()->optional(0.3)->secondaryAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postcode' => fake()->postcode(),
                'country' => fake()->country(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
            ],
            'shipping_address' => [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'company' => fake()->optional(0.2)->company(),
                'address_1' => fake()->streetAddress(),
                'address_2' => fake()->optional(0.3)->secondaryAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postcode' => fake()->postcode(),
                'country' => fake()->country(),
            ],
            'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer', 'cash_on_delivery']),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'order_notes' => fake()->optional(0.3)->randomElement([
                [
                    'customer_note' => fake()->sentence(),
                    'admin_note' => fake()->optional(0.5)->sentence(),
                ],
                null,
            ]),
        ];
    }

    /**
     * Create a pending order.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Create a completed order.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Create a cancelled order.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'payment_status' => 'failed',
        ]);
    }

    /**
     * Create a paid order.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }
}
