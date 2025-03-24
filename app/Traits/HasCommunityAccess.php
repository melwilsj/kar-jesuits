<?php

namespace App\Traits;

use App\Models\Community;

trait HasCommunityAccess
{
    public function canAccessCommunity(Community $community): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }

        if ($this->isPOSA() && $community->isCommonHouse()) {
            return true;
        }

        if ($community->isCommonHouse()) {
            return false; // Only POSA and superadmin can access common houses
        }

        // Check province access
        if ($community->province && $this->canAccessProvince($community->province)) {
            return true;
        }

        // Check region access if region exists
        if ($community->region && $this->canAccessRegion($community->region)) {
            return true;
        }

        return $this->hasRole('community_superior') && 
            $this->managedCommunities->contains($community);
    }

    public function canManageCommunity(Community $community): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }

        if ($community->isCommonHouse()) {
            return $this->isPOSA();
        }

        // Check province management access
        if ($community->province && $this->canManageProvince($community->province)) {
            return true;
        }

        // Check region management access if region exists
        if ($community->region && $this->canManageRegion($community->region)) {
            return true;
        }

        return $this->hasRole('community_superior') && 
            $this->managedCommunities->contains($community);
    }

    public function accessibleCommunities()
    {
        if ($this->hasRole('superadmin')) {
            return Community::query();
        }

        if ($this->isPOSA()) {
            return Community::commonHouses();
        }

        if ($this->isProvincial()) {
            return Community::inProvince($this->jesuit->province_id);
        }

        if ($this->isSuperior()) {
            return Community::where('id', $this->jesuit->current_community_id);
        }

        return Community::whereRaw('1 = 0');
    }
} 