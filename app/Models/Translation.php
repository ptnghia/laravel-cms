<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    /**
     * Scope a query to filter by locale.
     */
    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope a query to filter by group.
     */
    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get translation by key and locale.
     */
    public static function get(string $key, string $locale, string $group = 'general'): ?string
    {
        $translation = static::forLocale($locale)
                            ->forGroup($group)
                            ->where('key', $key)
                            ->first();

        return $translation?->value;
    }

    /**
     * Set translation for key and locale.
     */
    public static function set(string $key, string $value, string $locale, string $group = 'general'): self
    {
        return static::updateOrCreate(
            [
                'locale' => $locale,
                'group' => $group,
                'key' => $key,
            ],
            ['value' => $value]
        );
    }

    /**
     * Get all translations for a locale and group.
     */
    public static function getGroup(string $locale, string $group = 'general'): array
    {
        return static::forLocale($locale)
                    ->forGroup($group)
                    ->pluck('value', 'key')
                    ->toArray();
    }
}
