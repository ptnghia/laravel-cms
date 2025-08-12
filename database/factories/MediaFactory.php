<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = ['image', 'video', 'document', 'audio'];
        $fileType = fake()->randomElement($fileTypes);

        $extensions = [
            'image' => ['jpg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'avi', 'mov', 'webm'],
            'document' => ['pdf', 'doc', 'docx', 'txt'],
            'audio' => ['mp3', 'wav', 'ogg', 'flac'],
        ];

        $extension = fake()->randomElement($extensions[$fileType]);
        $fileName = fake()->slug() . '.' . $extension;
        $originalName = fake()->words(2, true) . '.' . $extension;

        return [
            'name' => fake()->words(2, true),
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => 'uploads/' . date('Y/m/') . $fileName,
            'mime_type' => $this->getMimeType($fileType, $extension),
            'file_type' => $fileType,
            'file_size' => fake()->numberBetween(1024, 10485760), // 1KB to 10MB
            'disk' => 'public',
            'path' => 'uploads/' . date('Y/m/') . $fileName,
            'metadata' => $this->getMetadata($fileType),
            'user_id' => \App\Models\User::factory(),
        ];
    }

    /**
     * Get mime type based on file type and extension.
     */
    private function getMimeType(string $fileType, string $extension): string
    {
        $mimeTypes = [
            'image' => [
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
            ],
            'video' => [
                'mp4' => 'video/mp4',
                'avi' => 'video/x-msvideo',
                'mov' => 'video/quicktime',
                'webm' => 'video/webm',
            ],
            'document' => [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
            ],
            'audio' => [
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg',
                'flac' => 'audio/flac',
            ],
        ];

        return $mimeTypes[$fileType][$extension] ?? 'application/octet-stream';
    }

    /**
     * Get metadata based on file type.
     */
    private function getMetadata(string $fileType): array
    {
        switch ($fileType) {
            case 'image':
                return [
                    'width' => fake()->numberBetween(200, 2000),
                    'height' => fake()->numberBetween(200, 2000),
                    'alt_text' => fake()->optional(0.6)->sentence(),
                ];
            case 'video':
                return [
                    'duration' => fake()->numberBetween(30, 3600), // 30 seconds to 1 hour
                    'width' => fake()->randomElement([1920, 1280, 854]),
                    'height' => fake()->randomElement([1080, 720, 480]),
                ];
            case 'audio':
                return [
                    'duration' => fake()->numberBetween(30, 600), // 30 seconds to 10 minutes
                    'bitrate' => fake()->randomElement([128, 192, 256, 320]),
                ];
            default:
                return [];
        }
    }

    /**
     * Create an image media.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
            'file_name' => fake()->slug() . '.jpg',
            'metadata' => [
                'width' => fake()->numberBetween(400, 2000),
                'height' => fake()->numberBetween(300, 1500),
                'alt_text' => fake()->sentence(),
            ],
        ]);
    }

    /**
     * Create a document media.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'document',
            'mime_type' => 'application/pdf',
            'file_name' => fake()->slug() . '.pdf',
        ]);
    }
}
