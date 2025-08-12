<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_posts' => $this->collection->count(),
                'published_posts' => $this->collection->where('status', 'published')->count(),
                'draft_posts' => $this->collection->where('status', 'draft')->count(),
                'scheduled_posts' => $this->collection->where('status', 'scheduled')->count(),
                'total_views' => $this->collection->sum('view_count'),
                'total_comments' => $this->collection->sum('comment_count'),
                'average_rating' => $this->collection->where('rating_count', '>', 0)->avg('rating_avg'),
            ],
            'links' => [
                'self' => $request->url(),
                'create' => url('/admin/posts/create'),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'timestamp' => now()->toISOString(),
        ];
    }
}
