<?php

namespace App\Traits;

use App\Models\Group;

trait HasGroupAccess
{
    public function canAccessGroup(Group $group): bool
    {
        return $this->hasRole('superadmin') || 
            $this->canAccessProvince($group->province) ||
            $group->members->contains($this->id);
    }

    public function canManageGroup(Group $group): bool
    {
        return $this->hasRole('superadmin') || 
            $this->canManageProvince($group->province);
    }
} 