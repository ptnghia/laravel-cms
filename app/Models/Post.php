<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image_id',
        'gallery',
        'author_id',
        'category_id',
        'status',
        'post_type',
        'published_at',
        'scheduled_at',
        'view_count',
        'comment_count',
        'rating_avg',
        'rating_count',
        'seo_meta',
        'meta_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'seo_meta' => 'array',
            'meta_data' => 'array',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'rating_avg' => 'float',
        ];
    }

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the category of the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the featured image of the post.
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    /**
     * Get the tags for the post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get the ratings for the post.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include scheduled posts.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '>', now());
    }

    /**
     * Scope a query to order by published date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Scope a query to order by view count.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Scope a query to filter by post type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('post_type', $type);
    }

    /**
     * Check if the post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               $this->published_at &&
               $this->published_at->isPast();
    }

    /**
     * Check if the post is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' &&
               $this->scheduled_at &&
               $this->scheduled_at->isFuture();
    }

    /**
     * Publish the post.
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish the post.
     */
    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Schedule the post for publishing.
     */
    public function schedule(\DateTime $dateTime): void
    {
        $this->update([
            'status' => 'scheduled',
            'scheduled_at' => $dateTime,
        ]);
    }

    /**
     * Increment the view count.
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Get the post's featured image URL.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featuredImage?->file_path ?
               asset('storage/' . $this->featuredImage->file_path) :
               null;
    }

    /**
     * Get the post's excerpt or truncated content.
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return \Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Get the post's reading time in minutes.
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // Average reading speed: 200 words per minute
    }

    /**
     * Sync tags and update their usage counts.
     */
    public function syncTags(array $tagNames): void
    {
        $tags = Tag::findOrCreateByNames($tagNames);
        $this->tags()->sync($tags->pluck('id'));

        // Update usage counts for all tags
        foreach ($tags as $tag) {
            $tag->update(['usage_count' => $tag->posts()->count()]);
        }
    }
}
