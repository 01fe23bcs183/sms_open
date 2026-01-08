<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Permission Model
 * 
 * Prompt 73: Create Permission Model with relationships to Role
 * using Spatie Permission package.
 * 
 * Extends Spatie's Permission model to add custom fields.
 * 
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $display_name
 * @property string|null $module
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'module',
        'description',
    ];

    /**
     * Scope a query to filter by module.
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Get the display name or fallback to name.
     */
    public function getDisplayNameAttribute($value): string
    {
        return $value ?? ucfirst(str_replace(['_', '.'], ' ', $this->name));
    }

    /**
     * Get all unique modules.
     */
    public static function getModules(): array
    {
        return static::query()
            ->whereNotNull('module')
            ->distinct()
            ->pluck('module')
            ->toArray();
    }
}
