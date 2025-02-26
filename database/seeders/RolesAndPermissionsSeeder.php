<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            'superadmin' => 'Super Administrator',
            'posa' => 'President of South Asia',
            'provincial' => 'Provincial',
            'province_admin' => 'Province Administrator',
            'region_admin' => 'Region Administrator',
            'community_superior' => 'Community Superior',
            'commission_head' => 'Commission Head',
            'member' => 'Regular Member'
        ];

        foreach ($roles as $slug => $name) {
            Role::create(['name' => $name, 'slug' => $slug]);
        }

        // Create permissions
        $permissions = [
            // User management
            'manage_users' => 'Manage Users',
            'view_users' => 'View Users',
            
            // Province management
            'manage_provinces' => 'Manage Provinces',
            'view_provinces' => 'View Provinces',
            
            // Region management
            'manage_regions' => 'Manage Regions',
            'view_regions' => 'View Regions',
            
            // Community management
            'manage_communities' => 'Manage Communities',
            'view_communities' => 'View Communities',
            'manage_common_houses' => 'Manage Common Houses',
            'view_common_houses' => 'View Common Houses',
            
            // Commission management
            'manage_commissions' => 'Manage Commissions',
            'view_commissions' => 'View Commissions',
            
            // Institution management
            'manage_institutions' => 'Manage Institutions',
            'view_institutions' => 'View Institutions',
            
            // Document generation
            'generate_catalogue' => 'Generate Catalogue',
            
            // Role assignment permissions
            'assign_provincial_roles' => 'Assign Provincial Level Roles',
            'assign_community_roles' => 'Assign Community Level Roles',
            'assign_institution_roles' => 'Assign Institution Level Roles',
            
            // Data access permissions
            'view_sensitive_data' => 'View Sensitive Data',
            'manage_sensitive_data' => 'Manage Sensitive Data',
            'view_province_data' => 'View Province Data',
            'manage_province_data' => 'Manage Province Data'
        ];

        foreach ($permissions as $slug => $name) {
            Permission::create(['name' => $name, 'slug' => $slug]);
        }

        // Assign permissions to roles
        $superadminRole = Role::where('slug', 'superadmin')->first();
        $superadminRole->permissions()->attach(Permission::all());

        // Assign specific permissions to roles
        $rolePermissions = [
            'posa' => [
                'manage_common_houses',
                'view_common_houses',
                'assign_community_roles'
            ],
            'provincial' => [
                'view_province_data',
                'manage_province_data',
                'view_sensitive_data',
                'assign_provincial_roles',
                'assign_community_roles',
                'manage_communities',
                'view_communities'
            ],
            'province_admin' => [
                'view_province_data',
                'manage_province_data',
                'manage_users',
                'view_users',
                'manage_communities',
                'view_communities',
                'manage_institutions',
                'view_institutions',
                'generate_catalogue'
            ],
            'community_superior' => [
                'assign_community_roles',
                'assign_institution_roles',
                'manage_communities',
                'view_communities',
                'view_sensitive_data'
            ]
        ];

        foreach ($rolePermissions as $role => $permissions) {
            $roleModel = Role::where('slug', $role)->first();
            $permissionModels = Permission::whereIn('slug', $permissions)->get();
            $roleModel->permissions()->attach($permissionModels);
        }
    }
} 