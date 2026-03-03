<?php
// app/Services/AuthorizationService.php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class AuthorizationService
{
    /**
     * Create a role for specific organization
     */
    public function createRole(Organization $organization, string $name, array $permissions = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'organization_id' => $organization->id,
        ]);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    /**
     * Create a permission for specific organization
     */
    public function createPermission(Organization $organization, string $name): Permission
    {
        return Permission::create([
            'name' => $name,
            'guard_name' => 'web',
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Assign role to user within organization
     */
    public function assignRoleToUser(User $user, Role $role): void
    {
        if ($user->organization_id !== $role->organization_id) {
            throw new \Exception('User and role must belong to the same organization');
        }

        $user->assignRole($role);
    }

    /**
     * Get all roles for an organization
     */
    public function getOrganizationRoles(Organization $organization)
    {
        return Role::where('organization_id', $organization->id)->get();
    }

    /**
     * Get all users with their roles in an organization
     */
    public function getOrganizationUsersWithRoles(Organization $organization)
    {
        return User::where('organization_id', $organization->id)
            ->with('roles')
            ->get();
    }
}