<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the setting value with proper type casting.
     */
    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'array', 'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Set the setting value with proper type handling.
     */
    public function setValueAttribute($value): void
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
            $this->attributes['type'] = 'json';
        } elseif (is_bool($value)) {
            $this->attributes['value'] = $value ? '1' : '0';
            $this->attributes['type'] = 'boolean';
        } elseif (is_int($value)) {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'integer';
        } elseif (is_float($value)) {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'float';
        } else {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'string';
        }
    }

    /**
     * Scope a query to only include settings for a specific group.
     */
    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope a query to only include public settings.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->typed_value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
            ]
        );
    }

    /**
     * Get all settings for a group as key-value pairs.
     */
    public static function getGroup(string $group): array
    {
        return static::forGroup($group)
                    ->get()
                    ->pluck('typed_value', 'key')
                    ->toArray();
    }

    /**
     * Get all public settings as key-value pairs.
     */
    public static function getPublic(): array
    {
        return static::public()
                    ->get()
                    ->pluck('typed_value', 'key')
                    ->toArray();
    }
}
