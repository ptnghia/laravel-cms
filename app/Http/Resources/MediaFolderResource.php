<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaFolderResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'parent' => new MediaFolderResource($this->whenLoaded('parent')),
            'children' => MediaFolderResource::collection($this->whenLoaded('children')),
            'media_count' => $this->when($this->relationLoaded('media'), $this->media->count()),

            // Computed fields
            'level' => $this->getLevel(),
            'has_children' => $this->when($this->relationLoaded('children'), $this->children ? $this->children->count() > 0 : false),
            'breadcrumb' => $this->when($this->shouldShowBreadcrumb($request), $this->getBreadcrumb()),
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }

    /**
     * Check if should show breadcrumb.
     */
    private function shouldShowBreadcrumb(Request $request): bool
    {
        return $request->has('include_breadcrumb');
    }

    /**
     * Get folder level in hierarchy.
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
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    /**
     * Check if user can edit this folder.
     */
    private function canEdit(Request $request): bool
    {
        $user = $request->user();
        return $user && (
            $user->hasRole('super_admin') ||
            $user->hasRole('admin') ||
            $user->hasRole('editor')
        );
    }

    /**
     * Check if user can delete this folder.
     */
    private function canDelete(Request $request): bool
    {
        return $this->canEdit($request) &&
               (!$this->children || !$this->children->count()) &&
               (!$this->media || !$this->media->count());
    }
}
