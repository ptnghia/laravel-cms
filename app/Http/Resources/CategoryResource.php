<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'description' => $this->description,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'seo_meta' => $this->when($this->canViewSeoMeta($request), $this->seo_meta),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'posts_count' => $this->when($this->relationLoaded('posts'), $this->posts->count()),

            // Computed fields
            'url' => url("/categories/{$this->slug}"),
            'level' => $this->getLevel(),
            'has_children' => $this->when($this->relationLoaded('children'), $this->children->count() > 0),
            'breadcrumb' => $this->when($this->shouldShowBreadcrumb($request), $this->getBreadcrumb()),
        ];
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
     * Check if should show breadcrumb.
     */
    private function shouldShowBreadcrumb(Request $request): bool
    {
        return $request->has('include_breadcrumb') ||
               $request->route()->getName() === 'categories.show';
    }

    /**
     * Get category level in hierarchy.
     */
    private function getLevel(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    /**
     * Get breadcrumb trail.
     */
    private function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $current = $this;

        while ($current) {
            array_unshift($breadcrumb, [
                'id' => $current->id,
                'name' => $current->name,
                'slug' => $current->slug,
                'url' => url("/categories/{$current->slug}"),
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }
}
