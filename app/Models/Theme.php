<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'path',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active theme.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get the active theme.
     */
    public static function getActive(): ?self
    {
        return static::active()->first();
    }

    /**
     * Activate this theme (and deactivate others).
     */
    public function activate(): void
    {
        static::where('active', true)->update(['active' => false]);
        $this->update(['active' => true]);
    }

    /**
     * Check if this theme is active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Get the theme's full path.
     */
    public function getFullPathAttribute(): string
    {
        return resource_path('views/themes/' . $this->path);
    }

    /**
     * Check if the theme directory exists.
     */
    public function exists(): bool
    {
        return is_dir($this->full_path);
    }
}
