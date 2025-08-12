<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_type',
        'file_size',
        'disk',
        'path',
        'metadata',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user who uploaded the media.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by file type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('file_type', $type);
    }

    /**
     * Scope a query to filter by mime type.
     */
    public function scopeOfMimeType($query, string $mimeType)
    {
        return $query->where('mime_type', $mimeType);
    }

    /**
     * Scope a query to only include images.
     */
    public function scopeImages($query)
    {
        return $query->where('file_type', 'image');
    }

    /**
     * Scope a query to only include videos.
     */
    public function scopeVideos($query)
    {
        return $query->where('file_type', 'video');
    }

    /**
     * Scope a query to only include documents.
     */
    public function scopeDocuments($query)
    {
        return $query->where('file_type', 'document');
    }

    /**
     * Get the media's full URL.
     */
    public function getUrlAttribute(): string
    {
        if ($this->disk === 'public') {
            return asset('storage/' . $this->path);
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the media's file size in human readable format.
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the media is an image.
     */
    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    /**
     * Check if the media is a video.
     */
    public function isVideo(): bool
    {
        return $this->file_type === 'video';
    }

    /**
     * Check if the media is a document.
     */
    public function isDocument(): bool
    {
        return $this->file_type === 'document';
    }

    /**
     * Get thumbnail URL for images.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->isImage()) {
            return null;
        }

        // If thumbnail exists in metadata
        if (isset($this->metadata['thumbnail'])) {
            return asset('storage/' . $this->metadata['thumbnail']);
        }

        // Return original image URL as fallback
        return $this->url;
    }

    /**
     * Get image dimensions if available.
     */
    public function getDimensionsAttribute(): ?array
    {
        if (!$this->isImage() || !isset($this->metadata['width'], $this->metadata['height'])) {
            return null;
        }

        return [
            'width' => $this->metadata['width'],
            'height' => $this->metadata['height'],
        ];
    }

    /**
     * Delete the media file from storage.
     */
    public function deleteFile(): bool
    {
        if (Storage::disk($this->disk)->exists($this->path)) {
            return Storage::disk($this->disk)->delete($this->path);
        }

        return true;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($media) {
            $media->deleteFile();
        });
    }

    /**
     * Determine file type from mime type.
     */
    public static function getFileTypeFromMime(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ];

        if (in_array($mimeType, $documentTypes)) {
            return 'document';
        }

        return 'other';
    }
}
