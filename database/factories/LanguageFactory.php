<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Language>
 */
class LanguageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt'],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français'],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español'],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch'],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語'],
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어'],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文'],
        ];

        $language = fake()->randomElement($languages);

        return [
            'code' => $language['code'],
            'name' => $language['name'],
            'native_name' => $language['native_name'],
            'is_active' => fake()->boolean(80), // 80% chance of being active
            'is_default' => false, // Will be set manually for one language
        ];
    }

    /**
     * Create an active language.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create the default language.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    /**
     * Create English language.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_active' => true,
        ]);
    }

    /**
     * Create Vietnamese language.
     */
    public function vietnamese(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'vi',
            'name' => 'Vietnamese',
            'native_name' => 'Tiếng Việt',
            'is_active' => true,
        ]);
    }
}
