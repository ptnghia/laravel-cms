<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location',
        'menu_items',
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
            'menu_items' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the menu items for the menu.
     */
    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order');
    }

    /**
     * Get all menu items (including nested).
     */
    public function allMenuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
                    ->orderBy('sort_order');
    }

    /**
     * Scope a query to only include active menus.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by location.
     */
    public function scopeForLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Get menu by location.
     */
    public static function getByLocation(string $location): ?self
    {
        return static::forLocation($location)->active()->first();
    }

    /**
     * Get the menu structure as a nested array.
     */
    public function getStructureAttribute(): array
    {
        return $this->menuItems()
                    ->with('descendants')
                    ->get()
                    ->toArray();
    }
}
