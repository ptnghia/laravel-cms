<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rating',
        'review',
        'user_id',
        'rateable_type',
        'rateable_id',
    ];

    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by rating value.
     */
    public function scopeForRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope a query to order by rating (highest first).
     */
    public function scopeHighestRated($query)
    {
        return $query->orderBy('rating', 'desc');
    }

    /**
     * Scope a query to order by rating (lowest first).
     */
    public function scopeLowestRated($query)
    {
        return $query->orderBy('rating', 'asc');
    }

    /**
     * Scope a query to only include ratings with reviews.
     */
    public function scopeWithReview($query)
    {
        return $query->whereNotNull('review')
                    ->where('review', '!=', '');
    }

    /**
     * Get the average rating for a rateable model.
     */
    public static function getAverageRating($rateable): float
    {
        return round($rateable->ratings()->avg('rating') ?: 0, 1);
    }

    /**
     * Get the rating count for a rateable model.
     */
    public static function getRatingCount($rateable): int
    {
        return $rateable->ratings()->count();
    }

    /**
     * Get rating distribution for a rateable model.
     */
    public static function getRatingDistribution($rateable): array
    {
        $distribution = [];

        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $rateable->ratings()
                                       ->where('rating', $i)
                                       ->count();
        }

        return $distribution;
    }

    /**
     * Check if the rating has a review.
     */
    public function hasReview(): bool
    {
        return !empty($this->review);
    }

    /**
     * Get the rating as stars (for display).
     */
    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}
