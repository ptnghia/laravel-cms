<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'user_id',
        'author_name',
        'author_email',
        'author_url',
        'content',
        'parent_id',
        'ip_address',
        'user_agent',
        'status',
        'metadata',
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
     * Get the commentable model (post, page, etc.).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who made the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the child comments.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
                    ->orderBy('created_at');
    }

    /**
     * Get all descendant comments.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Scope a query to only include approved comments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending comments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include spam comments.
     */
    public function scopeSpam($query)
    {
        return $query->where('status', 'spam');
    }

    /**
     * Scope a query to only include root comments (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to order by creation date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by creation date (oldest first).
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Check if the comment is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the comment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the comment is spam.
     */
    public function isSpam(): bool
    {
        return $this->status === 'spam';
    }

    /**
     * Approve the comment.
     */
    public function approve(): void
    {
        $this->update(['status' => 'approved']);
    }

    /**
     * Mark the comment as spam.
     */
    public function markAsSpam(): void
    {
        $this->update(['status' => 'spam']);
    }

    /**
     * Reject the comment.
     */
    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    /**
     * Get the comment author's name.
     */
    public function getAuthorNameAttribute($value): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $value ?: 'Anonymous';
    }

    /**
     * Get the comment author's email.
     */
    public function getAuthorEmailAttribute($value): ?string
    {
        if ($this->user) {
            return $this->user->email;
        }

        return $value;
    }

    /**
     * Get the comment author's avatar URL.
     */
    public function getAuthorAvatarAttribute(): string
    {
        if ($this->user && $this->user->avatar) {
            return $this->user->avatar_url;
        }

        $email = $this->author_email ?: 'anonymous@example.com';
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=mp&s=80';
    }

    /**
     * Check if this comment has replies.
     */
    public function hasReplies(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get the comment depth level.
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($comment) {
            // Update comment count on the commentable model
            if (method_exists($comment->commentable, 'updateCommentCount')) {
                $comment->commentable->updateCommentCount();
            }
        });

        static::deleted(function ($comment) {
            // Update comment count on the commentable model
            if (method_exists($comment->commentable, 'updateCommentCount')) {
                $comment->commentable->updateCommentCount();
            }
        });
    }
}
