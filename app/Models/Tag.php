<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
        'usage_count',
    ];

    /**
     * Get the posts that belong to the tag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tags');
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Decrement the usage count.
     */
    public function decrementUsage(): void
    {
        $this->decrement('usage_count');
    }

    /**
     * Scope a query to order by usage count.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    /**
     * Scope a query to order by name.
     */
    public function scopeAlphabetical($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Get popular tags.
     */
    public static function getPopular(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::popular()
                    ->where('usage_count', '>', 0)
                    ->limit($limit)
                    ->get();
    }

    /**
     * Find or create tags by names.
     */
    public static function findOrCreateByNames(array $names): \Illuminate\Database\Eloquent\Collection
    {
        $tags = collect();

        foreach ($names as $name) {
            $slug = \Str::slug($name);
            $tag = static::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
            $tags->push($tag);
        }

        return $tags;
    }

    /**
     * Get the tag's color or default color.
     */
    public function getColorAttribute($value): string
    {
        return $value ?: '#6B7280';
    }

    /**
     * Sync tags with posts and update usage counts.
     */
    public function syncWithPosts(array $postIds): void
    {
        $this->posts()->sync($postIds);
        $this->update(['usage_count' => $this->posts()->count()]);
    }
}
