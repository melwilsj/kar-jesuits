<?php

namespace App\Policies;

use App\Models\{User, ExternalAssignment, Community};

class ExternalAssignmentPolicy
{
    public function create(User $user, Community $community): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($community->isCommonHouse()) {
            return $user->isPOSA();
        }

        return $user->isProvincial() && 
            $community->province_id === $user->jesuit->province_id;
    }
} 