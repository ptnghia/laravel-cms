<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'title',
        'widget_type',
        'settings',
        'content',
        'position',
        'sort_order',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'content' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active widgets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by position.
     */
    public function scopeForPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope a query to filter by widget type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('widget_type', $type);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Get widgets for a specific position.
     */
    public static function getForPosition(string $position): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
                    ->forPosition($position)
                    ->ordered()
                    ->get();
    }

    /**
     * Check if the widget is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Activate the widget.
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the widget.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get a setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?: [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }
}
