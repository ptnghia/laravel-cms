<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
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
            'original_name' => $this->original_name,
            'filename' => $this->filename,
            'path' => $this->path,
            'url' => asset('storage/' . $this->path),
            'file_type' => $this->file_type,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'size_human' => $this->getHumanReadableSize(),
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'thumbnails' => $this->when($this->thumbnails, $this->getThumbnailUrls()),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'folder' => new MediaFolderResource($this->whenLoaded('folder')),
            'uploaded_by' => new UserResource($this->whenLoaded('uploader')),

            // Computed fields
            'is_image' => $this->file_type === 'image',
            'is_video' => $this->file_type === 'video',
            'is_audio' => $this->file_type === 'audio',
            'is_document' => $this->file_type === 'document',
            'can_edit' => $this->canEdit($request),
            'can_delete' => $this->canDelete($request),
            'download_url' => url("/api/media/{$this->id}/download"),
        ];
    }

    /**
     * Get human readable file size.
     */
    private function getHumanReadableSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get thumbnail URLs.
     */
    private function getThumbnailUrls(): array
    {
        if (!$this->thumbnails) {
            return [];
        }

        $urls = [];
        foreach ($this->thumbnails as $size => $path) {
            $urls[$size] = asset('storage/' . $path);
        }

        return $urls;
    }

    /**
     * Check if user can edit this media.
     */
    private function canEdit(Request $request): bool
    {
        $user = $request->user();
        if (!$user) return false;

        return $user->hasRole('super_admin') ||
               $user->hasRole('admin') ||
               $user->hasRole('editor') ||
               $user->id === $this->uploaded_by;
    }

    /**
     * Check if user can delete this media.
     */
    private function canDelete(Request $request): bool
    {
        $user = $request->user();
        if (!$user) return false;

        return $user->hasRole('super_admin') ||
               $user->hasRole('admin') ||
               $user->hasRole('editor');
    }
}
