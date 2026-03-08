<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Super admin can do everything
     */
    public function before(User $authUser)
    {
        if ($authUser->hasRole('super-admin')) {
            return true;
        }
    }

    /**
     * View user list
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('view users');
    }

    /**
     * View single user
     */
    public function view(User $authUser, User $user): bool
    {
        return $authUser->can('view users')
            && $authUser->branch_id === $user->branch_id;
    }

    /**
     * Create user
     */
    public function create(User $authUser): bool
    {
        return $authUser->can('create users');
    }

    /**
     * Update user
     */
    public function update(User $authUser, User $user): bool
    {
        return $authUser->can('edit users')
            && $authUser->branch_id === $user->branch_id;
    }

    /**
     * Delete user
     */
    public function delete(User $authUser, User $user): bool
    {
        return $authUser->can('delete users')
            && $authUser->branch_id === $user->branch_id;
    }

    /**
     * Restore user
     */
    public function restore(User $authUser, User $user): bool
    {
        return $authUser->can('delete users')
            && $authUser->branch_id === $user->branch_id;
    }

    /**
     * Assign role
     */
    public function assignRole(User $authUser, User $user): bool
    {
        return $authUser->can('edit users')
            && $authUser->branch_id === $user->branch_id;
    }

    /**
     * Sync permissions
     */
    public function syncPermissions(User $authUser, User $user): bool
    {
        return $authUser->can('edit users')
            && $authUser->branch_id === $user->branch_id;
    }
}