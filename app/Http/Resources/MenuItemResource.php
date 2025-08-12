<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
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
            'url' => $this->url,
            'target' => $this->target,
            'icon' => $this->icon,
            'css_class' => $this->css_class,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'children' => MenuItemResource::collection($this->whenLoaded('children')),
            'parent' => new MenuItemResource($this->whenLoaded('parent')),

            // Computed fields
            'level' => $this->getLevel(),
            'has_children' => $this->when($this->relationLoaded('children'), $this->children ? $this->children->count() > 0 : false),
            'is_external' => $this->isExternalUrl(),
            'full_url' => $this->getFullUrl(),
            'breadcrumb' => $this->when($this->shouldShowBreadcrumb($request), $this->getBreadcrumb()),
        ];
    }

    /**
     * Get menu item level in hierarchy.
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
     * Check if URL is external.
     */
    private function isExternalUrl(): bool
    {
        return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Get full URL (internal or external).
     */
    private function getFullUrl(): string
    {
        if ($this->isExternalUrl()) {
            return $this->url;
        }

        // Handle internal URLs
        if (str_starts_with($this->url, '/')) {
            return url($this->url);
        }

        return url('/' . $this->url);
    }

    /**
     * Check if should show breadcrumb.
     */
    private function shouldShowBreadcrumb(Request $request): bool
    {
        return $request->has('include_breadcrumb');
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
                'url' => $current->getFullUrl(),
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }
}
