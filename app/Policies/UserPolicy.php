<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewTimeTravel(User $user): bool
    {
        return $user->hasRole(['superadmin', 'province_admin', 'auditor']);
    }
} 