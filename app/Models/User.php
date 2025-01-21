<?php

namespace App\Models;

use App\Traits\{
    HasProvinceAccess,
    HasRegionAccess,
    HasCommunityAccess,
    HasCommissionAccess,
    HasGroupAccess,
    HasInstitutionAccess,
    HasRolesAndPermissions
};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens,
        HasRolesAndPermissions,
        HasProvinceAccess,
        HasRegionAccess,
        HasCommunityAccess,
        HasCommissionAccess,
        HasGroupAccess,
        HasInstitutionAccess,
        HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'profile_photo',
        'type',
        'is_external',
        'is_active',
        'province_id',
        'region_id',
        'current_community_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_external' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Profile relationships
    public function profile()
    {
        return $this->hasOne(JesuitProfile::class);
    }

    public function currentFormation()
    {
        return $this->hasOne(JesuitFormation::class)
            ->latest()
            ->where('end_date', null);
    }

    public function formationHistory()
    {
        return $this->hasMany(JesuitFormation::class);
    }

    // Assignment relationships
    public function currentCommunity()
    {
        return $this->belongsTo(Community::class, 'current_community_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function roleAssignments()
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function activeRoles()
    {
        return $this->hasMany(RoleAssignment::class)
            ->where('is_active', true)
            ->whereNull('end_date');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function externalAssignments()
    {
        return $this->hasMany(ExternalAssignment::class);
    }

    public function provinceTransfers()
    {
        return $this->hasMany(ProvinceTransfer::class);
    }

    // Helper methods
    public function isProvincial(): bool
    {
        return $this->activeRoles()
            ->whereHasMorph('assignable', [Province::class])
            ->where('role_type_id', RoleType::where('name', 'Provincial')->first()->id)
            ->exists();
    }

    public function isSuperior(): bool
    {
        return $this->activeRoles()
            ->whereHasMorph('assignable', [Community::class])
            ->whereIn('role_type_id', RoleType::whereIn('name', ['Superior', 'Rector'])->pluck('id'))
            ->exists();
    }

    public function canViewMemberDetails(User $member): bool
    {
        if ($this->hasRole('superadmin')) return true;
        
        if ($this->isProvincial() && $member->province_id === $this->province_id) return true;
        
        if ($this->isSuperior() && $member->current_community_id === $this->currentCommunity->id) return true;
        
        return false;
    }
}
