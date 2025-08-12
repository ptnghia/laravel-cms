<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MediaFolder>
 */
class MediaFolderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $folderNames = [
            'Images', 'Documents', 'Videos', 'Audio', 'Archives',
            'Photos', 'Graphics', 'Templates', 'Uploads', 'Assets',
            'Gallery', 'Portfolio', 'Blog Images', 'Product Photos',
            'User Avatars', 'Banners', 'Icons', 'Logos'
        ];

        return [
            'name' => fake()->randomElement($folderNames),
            'parent_id' => null, // Will be set manually for subfolders
            'user_id' => \App\Models\User::factory(),
        ];
    }

    /**
     * Create a root folder (no parent).
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Create a subfolder.
     */
    public function subfolder(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => \App\Models\MediaFolder::factory(),
        ]);
    }

    /**
     * Create a folder with specific parent.
     */
    public function withParent($parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Create a folder for specific file type.
     */
    public function forFileType(string $type): static
    {
        $names = [
            'image' => ['Images', 'Photos', 'Gallery', 'Pictures'],
            'video' => ['Videos', 'Movies', 'Clips'],
            'document' => ['Documents', 'Files', 'PDFs'],
            'audio' => ['Audio', 'Music', 'Sounds'],
        ];

        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement($names[$type] ?? ['Files']),
        ]);
    }

    /**
     * Create a user-specific folder.
     */
    public function forUser($userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
            'name' => 'User ' . $userId . ' Files',
        ]);
    }
}
