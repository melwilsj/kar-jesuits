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

        if ($this->canAccessProvince($community->province) || 
            $this->canAccessRegion($community->region)) {
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

        return $this->canManageProvince($community->province) ||
            $this->canManageRegion($community->region) ||
            ($this->hasRole('community_superior') && 
            $this->managedCommunities->contains($community));
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