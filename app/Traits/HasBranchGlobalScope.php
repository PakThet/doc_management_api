<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

trait HasBranchGlobalScope
{
    protected static function bootHasBranchGlobalScope()
    {
        static::addGlobalScope('branch', function (Builder $query) {
            /** @var User|null $user */
            $user = Auth::user();

            if (!$user) {
                return;
            }

            // Super-admin sees all branches
            if ($user->hasRole('super-admin')) {
                return;
            }

            $query->where('branch_id', $user->branch_id);
        });
    }
}