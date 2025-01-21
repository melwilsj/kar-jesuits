<?php

namespace App\Traits;

use App\Models\Region;

trait HasRegionAccess
{
    public function canAccessRegion(Region $region): bool
    {
        if ($this->hasRole('superadmin') || $this->canAccessProvince($region->province)) {
            return true;
        }

        if ($this->hasRole('region_admin')) {
            return $this->regions->contains($region);
        }

        if ($this->hasRole('community_superior')) {
            return $this->managedCommunities->pluck('region_id')->contains($region->id);
        }

        return false;
    }

    public function canManageRegion(Region $region): bool
    {
        return $this->hasRole('superadmin') || 
            ($this->hasRole('province_admin') && $this->provinces->contains($region->province)) ||
            ($this->hasRole('region_admin') && $this->regions->contains($region));
    }
}