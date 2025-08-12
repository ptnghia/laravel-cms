<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'last_login_ip' => $this->when($this->canViewSensitiveData($request), $this->last_login_ip),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'posts_count' => $this->when($this->relationLoaded('posts'), $this->posts->count()),
            'comments_count' => $this->when($this->relationLoaded('comments'), $this->comments->count()),

            // Conditional fields for admin
            'permissions' => $this->when(
                $this->canViewPermissions($request) && $this->relationLoaded('roles'),
                $this->roles->flatMap->permissions->pluck('name')->unique()->values()
            ),
        ];
    }

    /**
     * Check if user can view sensitive data.
     */
    private function canViewSensitiveData(Request $request): bool
    {
        $user = $request->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->id === $this->id);
    }

    /**
     * Check if user can view permissions.
     */
    private function canViewPermissions(Request $request): bool
    {
        $user = $request->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin'));
    }
}
