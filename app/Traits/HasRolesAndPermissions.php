<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRolesAndPermissions
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions(): array
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->map(function ($role) {
                return $role->permissions;
            })
            ->flatten()
            ->pluck('slug')
            ->unique()
            ->toArray();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()
            ->where('slug', $role)
            ->exists();
    }

    public function hasPermissionTo(string $permission): bool
    {
        return $this->hasPermission($permission);
    }

    public function hasPermissionThroughRole(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    protected function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions());
    }
} 