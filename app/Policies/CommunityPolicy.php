<?php

namespace App\Policies;

use App\Models\{User, Community};

class CommunityPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view communities list
    }

    public function view(User $user, Community $community): bool
    {
        return $user->canAccessCommunity($community);
    }

    public function manage(User $user, Community $community): bool
    {
        return $user->canManageCommunity($community);
    }

    public function assignSuperior(User $user, Community $community): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($community->isCommonHouse()) {
            return $user->isPOSA();
        }

        return $user->canManageProvince($community->province);
    }

    public function viewMembers(User $user, Community $community): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($community->isCommonHouse()) {
            return $user->isPOSA();
        }

        return $user->canAccessProvince($community->province) || 
            $user->canAccessRegion($community->region) ||
            ($user->hasRole('community_superior') && 
             $user->managedCommunities->contains($community));
    }
} 