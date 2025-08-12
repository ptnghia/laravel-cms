<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'user_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    /**
     * Get all descendant folders.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Scope a query to only include root folders (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to order by name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Get the folder tree structure.
     */
    public static function getTree(): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('descendants')
                    ->root()
                    ->ordered()
                    ->get();
    }

    /**
     * Get the full path of the folder (including parents).
     */
    public function getFullPathAttribute(): string
    {
        $path = collect();
        $folder = $this;

        while ($folder) {
            $path->prepend($folder->name);
            $folder = $folder->parent;
        }

        return $path->implode('/');
    }

    /**
     * Check if this folder has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this folder has media files.
     */
    public function hasMedia(): bool
    {
        return $this->media()->exists();
    }

    /**
     * Get total media count including subfolders.
     */
    public function getTotalMediaCountAttribute(): int
    {
        $count = $this->media()->count();

        foreach ($this->children as $child) {
            $count += $child->total_media_count;
        }

        return $count;
    }

    /**
     * Check if this folder is a descendant of another folder.
     */
    public function isDescendantOf(MediaFolder $folder): bool
    {
        $parent = $this->parent;

        while ($parent) {
            if ($parent->id === $folder->id) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }
}
