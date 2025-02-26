<?php

namespace App\Traits;

use App\Models\Region;
use App\Models\RoleType;

trait HasRegionAccess
{
    public function canAccessRegion(Region $region): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }

        if ($this->isProvincial()) {
            return $this->jesuit->province->regions->contains($region);
        }

        // Regional superior can only access their own region
        return $this->managedRegions->contains($region);
    }

    public function canManageRegion(Region $region): bool
    {
        return $this->hasRole('superadmin') || 
            ($this->hasRole('province_admin') && $this->provinces->contains($region->province)) ||
            ($this->hasRole('region_admin') && $this->regions->contains($region));
    }

    public function managedRegions()
    {
        return $this->jesuit?->activeRoles()
            ->whereHasMorph('assignable', [Region::class])
            ->where('role_type_id', RoleType::where('name', 'Regional Superior')->first()->id)
            ->get()
            ->map(fn($role) => $role->assignable);
    }
}