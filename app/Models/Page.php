<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
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
        'template',
        'page_builder_data',
        'status',
        'published_at',
        'parent_id',
        'sort_order',
        'is_active',
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
            'page_builder_data' => 'array',
            'seo_meta' => 'array',
            'meta_data' => 'array',
            'published_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the parent page.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get the child pages.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')
                    ->orderBy('sort_order')
                    ->orderBy('title');
    }

    /**
     * Get all descendant pages.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the comments for the page.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Scope a query to only include published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    });
    }

    /**
     * Scope a query to only include active pages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include root pages (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to order by sort order and title.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Scope a query to filter by template.
     */
    public function scopeWithTemplate($query, string $template)
    {
        return $query->where('template', $template);
    }

    /**
     * Check if the page is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               $this->is_active &&
               (!$this->published_at || $this->published_at->isPast());
    }

    /**
     * Publish the page.
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish the page.
     */
    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
            'is_active' => false,
        ]);
    }

    /**
     * Get the page tree structure.
     */
    public static function getTree(): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('descendants')
                    ->root()
                    ->active()
                    ->ordered()
                    ->get();
    }

    /**
     * Get the full path of the page (including parents).
     */
    public function getFullPathAttribute(): string
    {
        $path = collect();
        $page = $this;

        while ($page) {
            $path->prepend($page->title);
            $page = $page->parent;
        }

        return $path->implode(' > ');
    }

    /**
     * Get the page's breadcrumb trail.
     */
    public function getBreadcrumbsAttribute(): array
    {
        $breadcrumbs = [];
        $page = $this;

        while ($page) {
            $breadcrumbs[] = [
                'title' => $page->title,
                'slug' => $page->slug,
                'url' => route('pages.show', $page->slug),
            ];
            $page = $page->parent;
        }

        return array_reverse($breadcrumbs);
    }

    /**
     * Check if this page has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this page is a descendant of another page.
     */
    public function isDescendantOf(Page $page): bool
    {
        $parent = $this->parent;

        while ($parent) {
            if ($parent->id === $page->id) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    /**
     * Get the page's template file path.
     */
    public function getTemplatePathAttribute(): string
    {
        return $this->template ?: 'default';
    }

    /**
     * Update comment count for the page.
     */
    public function updateCommentCount(): void
    {
        $count = $this->comments()->approved()->count();
        $this->update(['comment_count' => $count]);
    }
}
