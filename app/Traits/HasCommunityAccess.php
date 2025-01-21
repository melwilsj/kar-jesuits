<?php

namespace App\Traits;

use App\Models\Community;

trait HasCommunityAccess
{
    public function canAccessCommunity(Community $community): bool
    {
        if ($this->hasRole('superadmin') || 
            $this->canAccessProvince($community->province) || 
            $this->canAccessRegion($community->region)) {
            return true;
        }

        return $this->hasRole('community_superior') && 
            $this->managedCommunities->contains($community);
    }

    public function canManageCommunity(Community $community): bool
    {
        return $this->hasRole('superadmin') || 
            $this->canManageProvince($community->province) ||
            $this->canManageRegion($community->region) ||
            ($this->hasRole('community_superior') && $this->managedCommunities->contains($community));
    }
} 