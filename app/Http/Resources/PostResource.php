<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->when($this->shouldShowFullContent($request), $this->content),
            'excerpt' => $this->excerpt,
            'featured_image' => $this->when(
                $this->featured_image_id,
                new MediaResource($this->whenLoaded('featuredImage'))
            ),
            'gallery' => $this->gallery,
            'status' => $this->status,
            'post_type' => $this->post_type,
            'published_at' => $this->published_at?->toISOString(),
            'scheduled_at' => $this->when(
                $this->canViewScheduledDate($request),
                $this->scheduled_at?->toISOString()
            ),
            'view_count' => $this->view_count,
            'comment_count' => $this->comment_count,
            'rating_avg' => $this->rating_avg,
            'rating_count' => $this->rating_count,
            'seo_meta' => $this->when($this->canViewSeoMeta($request), $this->seo_meta),
            'meta_data' => $this->when($this->canViewMetaData($request), $this->meta_data),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'author' => new UserResource($this->whenLoaded('author')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),

            // Computed fields
            'url' => url("/posts/{$this->slug}"),
            'edit_url' => $this->when(
                $this->canEdit($request),
                url("/admin/posts/{$this->id}/edit")
            ),
            'reading_time' => $this->getReadingTime(),
            'is_published' => $this->status === 'published',
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }

    /**
     * Check if should show full content.
     */
    private function shouldShowFullContent(Request $request): bool
    {
        // Show full content in single post view or admin area
        return $request->route()->getName() === 'posts.show' ||
               str_contains($request->path(), 'admin/');
    }

    /**
     * Check if user can view scheduled date.
     */
    private function canViewScheduledDate(Request $request): bool
    {
        $user = $request->user();
        return $user && (
            $user->hasRole('super_admin') ||
            $user->hasRole('admin') ||
            $user->hasRole('editor') ||
            $user->id === $this->author_id
        );
    }

    /**
     * Check if user can view SEO meta.
     */
    private function canViewSeoMeta(Request $request): bool
    {
        $user = $request->user();
        return $user && (
            $user->hasRole('super_admin') ||
            $user->hasRole('admin') ||
            $user->hasRole('editor')
        );
    }

    /**
     * Check if user can view meta data.
     */
    private function canViewMetaData(Request $request): bool
    {
        return $this->canViewSeoMeta($request);
    }

    /**
     * Check if user can edit this post.
     */
    private function canEdit(Request $request): bool
    {
        $user = $request->user();
        if (!$user) return false;

        return $user->hasRole('super_admin') ||
               $user->hasRole('admin') ||
               $user->hasRole('editor') ||
               ($user->hasRole('author') && $user->id === $this->author_id);
    }

    /**
     * Check if user can delete this post.
     */
    private function canDelete(Request $request): bool
    {
        $user = $request->user();
        if (!$user) return false;

        return $user->hasRole('super_admin') ||
               $user->hasRole('admin') ||
               $user->hasRole('editor');
    }

    /**
     * Calculate reading time in minutes.
     */
    private function getReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // Assuming 200 words per minute
    }
}
