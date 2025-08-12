<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'module',
    ];

    /**
     * Get the roles that belong to the permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
                    ->withTimestamps();
    }

    /**
     * Get the permission's display name or name if display_name is not set.
     */
    public function getDisplayAttribute(): string
    {
        return $this->display_name ?: $this->name;
    }

    /**
     * Scope a query to only include permissions for a specific module.
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope a query to group permissions by module.
     */
    public function scopeGroupedByModule($query)
    {
        return $query->orderBy('module')->orderBy('name');
    }

    /**
     * Get all permissions grouped by module.
     */
    public static function getGroupedByModule(): array
    {
        return static::groupedByModule()
                    ->get()
                    ->groupBy('module')
                    ->toArray();
    }
}
