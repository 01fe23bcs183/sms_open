<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Role Model
 * 
 * Prompt 72: Create Role Model with relationships to Permission and User
 * using Spatie Permission package.
 * 
 * Extends Spatie's Role model to add custom fields and relationships.
 * 
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $display_name
 * @property string|null $description
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Role extends SpatieRole
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
        'description',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if the role is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the display name or fallback to name.
     */
    public function getDisplayNameAttribute($value): string
    {
        return $value ?? ucfirst($this->name);
    }
}
