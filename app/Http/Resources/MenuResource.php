<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            'location' => $this->location,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'menu_items' => MenuItemResource::collection($this->whenLoaded('menuItems')),

            // Computed fields
            'items_count' => $this->when($this->relationLoaded('menuItems'), $this->menuItems ? $this->menuItems->count() : 0),
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
        ];
    }

    /**
     * Check if user can edit this menu.
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
     * Check if user can delete this menu.
     */
    private function canDelete(Request $request): bool
    {
        return $this->canEdit($request);
    }
}
