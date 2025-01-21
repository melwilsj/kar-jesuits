<?php

namespace App\Traits;

use App\Models\Commission;

trait HasCommissionAccess
{
    public function canAccessCommission(Commission $commission): bool
    {
        if ($this->hasRole('superadmin') || $this->canAccessProvince($commission->province)) {
            return true;
        }

        return $this->hasRole('commission_head') && 
            $commission->members()->where('user_id', $this->id)
            ->where('role', 'head')->exists();
    }

    public function canManageCommission(Commission $commission): bool
    {
        return $this->hasRole('superadmin') || 
            $this->canManageProvince($commission->province) ||
            ($this->hasRole('commission_head') && 
                $commission->members()->where('user_id', $this->id)
                ->where('role', 'head')->exists());
    }
} 