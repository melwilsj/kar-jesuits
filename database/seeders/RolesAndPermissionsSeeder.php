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
            
            // Commission management
            'manage_commissions' => 'Manage Commissions',
            'view_commissions' => 'View Commissions',
            
            // Group management
            'manage_groups' => 'Manage Groups',
            'view_groups' => 'View Groups',
            
            // Institution management
            'manage_institutions' => 'Manage Institutions',
            'view_institutions' => 'View Institutions',
            
            // Document generation
            'generate_catalogue' => 'Generate Catalogue',
        ];

        foreach ($permissions as $slug => $name) {
            Permission::create(['name' => $name, 'slug' => $slug]);
        }

        // Assign permissions to roles
        $superadminRole = Role::where('slug', 'superadmin')->first();
        $superadminRole->permissions()->attach(Permission::all());
    }
} 