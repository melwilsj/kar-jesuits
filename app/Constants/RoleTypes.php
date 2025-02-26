<?php

namespace App\Constants;

class RoleTypes
{
    const SUPERIOR_ROLES = ['Superior', 'Rector', 'Coordinator'];
    const ADMINISTRATIVE_ROLES = ['Provincial', 'POSA'];
    
    public static function isSuperiorRole(string $role): bool
    {
        return in_array($role, self::SUPERIOR_ROLES);
    }
} 