<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'content' => $this->content,
            'status' => $this->status,
            'parent_id' => $this->parent_id,
            'commentable_type' => $this->commentable_type,
            'commentable_id' => $this->commentable_id,
            'author_name' => $this->author_name,
            'author_email' => $this->when($this->canViewEmail($request), $this->author_email),
            'author_website' => $this->author_website,
            'author_ip' => $this->when($this->canViewSensitiveData($request), $this->author_ip),
            'user_agent' => $this->when($this->canViewSensitiveData($request), $this->user_agent),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'commentable' => $this->when($this->relationLoaded('commentable'), function () {
                if ($this->commentable_type === 'App\\Models\\Post') {
                    return new PostResource($this->commentable);
                } elseif ($this->commentable_type === 'App\\Models\\Page') {
                    return new PageResource($this->commentable);
                }
                return null;
            }),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),

            // Computed fields
            'is_guest' => !$this->user_id,
            'is_approved' => $this->status === 'approved',
            'is_spam' => $this->status === 'spam',
            'replies_count' => $this->when($this->relationLoaded('replies'), $this->replies ? $this->replies->count() : 0),
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
            'can_approve' => $this->canApprove($request),
            'avatar' => $this->getAvatar(),
            'time_ago' => $this->created_at->diffForHumans(),
        ];
    }

    /**
     * Check if user can view email.
     */
    private function canViewEmail(Request $request): bool
    {
        $user = $request->user();
        return $user && (
            $user->hasRole('super_admin') ||
            $user->hasRole('admin') ||
            $user->hasRole('editor') ||
            $user->id === $this->user_id
        );
    }

    /**
     * Check if user can view sensitive data.
     */
    private function canViewSensitiveData(Request $request): bool
    {
        $user = $request->user();
        return $user && (
            $user->hasRole('super_admin') ||
            $user->hasRole('admin')
        );
    }

    /**
     * Check if user can edit this comment.
     */
    private function canEdit(Request $request): bool
    {
        $user = $request->user();
        if (!$user) return false;

        return $user->hasRole('super_admin') ||
               $user->hasRole('admin') ||
               $user->hasRole('editor') ||
               $user->id === $this->user_id;
    }

    /**
     * Check if user can delete this comment.
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
     * Check if user can approve this comment.
     */
    private function canApprove(Request $request): bool
    {
        return $this->canDelete($request);
    }

    /**
     * Get avatar for comment author.
     */
    private function getAvatar(): string
    {
        if ($this->user && $this->user->avatar) {
            return asset('storage/' . $this->user->avatar);
        }

        // Generate Gravatar for guest comments
        $hash = md5(strtolower(trim($this->author_email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=40";
    }
}
