<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;

class EmployeePolicy
{
    public function before(User $authUser)
    {
        if ($authUser->hasRole('super-admin')) {
            return true;
        }
    }
    
    public function view(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('view employees');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create employees');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('edit employees');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('delete employees');
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('delete employees');
    }
}