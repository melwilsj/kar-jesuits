<?php

namespace App\Policies;

use App\Models\{User, RoleAssignment, Community};
use App\Constants\RoleTypes;

class RoleAssignmentPolicy
{
    public function create(User $user, string $assignableType, int $assignableId): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($assignableType === Community::class) {
            $community = Community::findOrFail($assignableId);
            
            if ($community->isCommonHouse()) {
                return $user->isPOSA();
            }

            return $user->canManageCommunity($community);
        }

        return $user->hasRole('province_admin');
    }
} 