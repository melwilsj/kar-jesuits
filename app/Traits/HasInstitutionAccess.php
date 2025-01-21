<?php

namespace App\Traits;

use App\Models\Institution;

trait HasInstitutionAccess
{
    public function canAccessInstitution(Institution $institution): bool
    {
        return $this->hasRole('superadmin') || 
            $this->canAccessCommunity($institution->community);
    }

    public function canManageInstitution(Institution $institution): bool
    {
        return $this->hasRole('superadmin') || 
            $this->canManageCommunity($institution->community);
    }
} 