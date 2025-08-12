<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Laravel CMS', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'A powerful content management system', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_logo', 'value' => '/images/logo.png', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'general', 'is_public' => false],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'posts_per_page', 'value' => '10', 'type' => 'integer', 'group' => 'content', 'is_public' => true],
            ['key' => 'allow_comments', 'value' => '1', 'type' => 'boolean', 'group' => 'content', 'is_public' => true],
            ['key' => 'moderate_comments', 'value' => '1', 'type' => 'boolean', 'group' => 'content', 'is_public' => false],
            ['key' => 'smtp_host', 'value' => 'smtp.gmail.com', 'type' => 'string', 'group' => 'email', 'is_public' => false],
            ['key' => 'smtp_port', 'value' => '587', 'type' => 'integer', 'group' => 'email', 'is_public' => false],
        ];

        $setting = fake()->randomElement($settings);

        return [
            'key' => $setting['key'],
            'value' => $setting['value'],
            'type' => $setting['type'],
            'group' => $setting['group'],
            'description' => fake()->optional(0.7)->sentence(),
            'is_public' => $setting['is_public'],
        ];
    }

    /**
     * Create a public setting.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Create a private setting.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Create a setting for a specific group.
     */
    public function forGroup(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }
}
