<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

trait HasBranchScope
{
    public function scopeVisible(Builder $query)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return;
        }

        if ($user->hasRole('super-admin')) {
            return;
        }


        return $query->where('branch_id', $user->branch_id);
    }
}
