<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Revision extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'revisionable_type',
        'revisionable_id',
        'user_id',
        'key',
        'old_value',
        'new_value',
    ];

    /**
     * Get the revisionable model (post, page, etc.).
     */
    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who made the revision.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by revisionable type.
     */
    public function scopeForRevisionableType($query, string $type)
    {
        return $query->where('revisionable_type', $type);
    }

    /**
     * Scope a query to filter by key.
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope a query to order by latest first.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Create a revision for a model change.
     */
    public static function createRevision($model, string $key, $oldValue, $newValue, ?User $user = null): self
    {
        return static::create([
            'revisionable_type' => get_class($model),
            'revisionable_id' => $model->id,
            'user_id' => $user?->id ?? auth()->id(),
            'key' => $key,
            'old_value' => is_array($oldValue) || is_object($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) || is_object($newValue) ? json_encode($newValue) : $newValue,
        ]);
    }

    /**
     * Get the old value with proper type casting.
     */
    public function getOldValueAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * Get the new value with proper type casting.
     */
    public function getNewValueAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * Check if this revision represents a creation.
     */
    public function isCreation(): bool
    {
        return $this->key === 'created' || is_null($this->old_value);
    }

    /**
     * Check if this revision represents a deletion.
     */
    public function isDeletion(): bool
    {
        return $this->key === 'deleted' || is_null($this->new_value);
    }

    /**
     * Get a human-readable description of the change.
     */
    public function getDescriptionAttribute(): string
    {
        if ($this->isCreation()) {
            return "Created {$this->key}";
        }

        if ($this->isDeletion()) {
            return "Deleted {$this->key}";
        }

        return "Changed {$this->key} from '{$this->old_value}' to '{$this->new_value}'";
    }
}
