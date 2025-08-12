<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
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
            'template' => $this->template,
            'page_builder_data' => $this->when($this->canViewBuilderData($request), $this->page_builder_data),
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'seo_meta' => $this->when($this->canViewSeoMeta($request), $this->seo_meta),
            'meta_data' => $this->when($this->canViewMetaData($request), $this->meta_data),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'parent' => new PageResource($this->whenLoaded('parent')),
            'children' => PageResource::collection($this->whenLoaded('children')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),

            // Computed fields
            'url' => url("/pages/{$this->slug}"),
            'edit_url' => $this->when(
                $this->canEdit($request),
                url("/admin/pages/{$this->id}/edit")
            ),
            'level' => $this->getLevel(),
            'has_children' => $this->when($this->relationLoaded('children'), $this->children ? $this->children->count() > 0 : false),
            'breadcrumb' => $this->when($this->shouldShowBreadcrumb($request), $this->getBreadcrumb()),
            'is_published' => $this->status === 'published' && $this->is_active,
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }

    /**
     * Check if should show full content.
     */
    private function shouldShowFullContent(Request $request): bool
    {
        return $request->route()->getName() === 'pages.show' ||
               str_contains($request->path(), 'admin/');
    }

    /**
     * Check if user can view page builder data.
     */
    private function canViewBuilderData(Request $request): bool
    {
        $user = $request->user();
        return $user && (
            $user->hasRole('super_admin') ||
            $user->hasRole('admin') ||
            $user->hasRole('editor')
        );
    }

    /**
     * Check if user can view SEO meta.
     */
    private function canViewSeoMeta(Request $request): bool
    {
        return $this->canViewBuilderData($request);
    }

    /**
     * Check if user can view meta data.
     */
    private function canViewMetaData(Request $request): bool
    {
        return $this->canViewBuilderData($request);
    }

    /**
     * Check if should show breadcrumb.
     */
    private function shouldShowBreadcrumb(Request $request): bool
    {
        return $request->has('include_breadcrumb') ||
               $request->route()->getName() === 'pages.show';
    }

    /**
     * Check if user can edit this page.
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
     * Check if user can delete this page.
     */
    private function canDelete(Request $request): bool
    {
        return $this->canEdit($request) && (!$this->children || !$this->children->count());
    }

    /**
     * Get page level in hierarchy.
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
                'title' => $current->title,
                'slug' => $current->slug,
                'url' => url("/pages/{$current->slug}"),
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }
}
