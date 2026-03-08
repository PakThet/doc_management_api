<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Document;

class DocumentPolicy
{
    /**
     * Run before any other policy method.
     * If it returns true/false, Laravel will skip other checks.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }
    }

    public function view(User $user, Document $document): bool
    {
        return $user->branch_id === $document->branch_id;
    }

    public function update(User $user, Document $document): bool
    {
        return $user->hasPermissionTo('edit documents') 
            && $user->branch_id === $document->branch_id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->hasPermissionTo('delete documents') 
            && $user->branch_id === $document->branch_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create documents');
    }

    public function restore(User $user, Document $document): bool
    {
        return $user->hasPermissionTo('delete documents') 
            && $user->branch_id === $document->branch_id;
    }
}