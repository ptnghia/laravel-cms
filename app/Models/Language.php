<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'is_active',
        'is_default',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active languages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get the default language.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default language.
     */
    public static function getDefault(): ?self
    {
        return static::default()->first();
    }

    /**
     * Get all active languages.
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->orderBy('name')->get();
    }

    /**
     * Set this language as default (and unset others).
     */
    public function setAsDefault(): void
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true, 'is_active' => true]);
    }

    /**
     * Get the language's display name (native name or name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->native_name ?: $this->name;
    }
}
