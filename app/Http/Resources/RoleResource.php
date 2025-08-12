<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'display_name' => $this->display_name,
            'description' => $this->description,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'users_count' => $this->when($this->relationLoaded('users'), $this->users->count()),

            // Conditional fields
            'is_system_role' => $this->when(
                $this->canViewSystemInfo($request),
                in_array($this->name, ['super_admin', 'admin', 'editor', 'author', 'user'])
            ),
        ];
    }

    /**
     * Check if user can view system information.
     */
    private function canViewSystemInfo(Request $request): bool
    {
        $user = $request->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin'));
    }
}
