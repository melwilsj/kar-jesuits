<?php

namespace App\Traits;

use App\Models\Province;

trait HasProvinceAccess
{
    public function canAccessProvince(Province $province): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }

        if ($this->hasRole('province_admin')) {
            return $this->provinces->contains($province);
        }

        if ($this->hasRole('region_admin')) {
            return $this->regions->pluck('province_id')->contains($province->id);
        }

        if ($this->hasRole('community_superior')) {
            return $this->managedCommunities->pluck('province_id')->contains($province->id);
        }

        return false;
    }

    public function canManageProvince(Province $province): bool
    {
        return $this->hasRole('superadmin') || 
            ($this->hasRole('province_admin') && $this->provinces->contains($province));
    }
} 