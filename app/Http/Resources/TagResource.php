<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'usage_count' => $this->usage_count,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'posts_count' => $this->when($this->relationLoaded('posts'), $this->posts->count()),

            // Computed fields
            'url' => url("/tags/{$this->slug}"),
            'is_popular' => $this->usage_count > 10,
            'color_class' => $this->getColorClass(),
        ];
    }

    /**
     * Get CSS class for tag color.
     */
    private function getColorClass(): string
    {
        if (!$this->color) {
            return 'bg-gray-500';
        }

        // Convert hex color to Tailwind class (simplified)
        $colorMap = [
            '#EF4444' => 'bg-red-500',
            '#F97316' => 'bg-orange-500',
            '#EAB308' => 'bg-yellow-500',
            '#22C55E' => 'bg-green-500',
            '#3B82F6' => 'bg-blue-500',
            '#8B5CF6' => 'bg-purple-500',
            '#EC4899' => 'bg-pink-500',
        ];

        return $colorMap[$this->color] ?? 'bg-gray-500';
    }
}
