<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasOrganizationScope
{
    protected static function bootHasOrganizationScope()
    {
        static::addGlobalScope('organization', function (Builder $builder) {

            if (!Auth::check()) {
                return;
            }

            $user = Auth::user();

            // 👑 Super Admin (organization_id = null) → NO FILTER
            if ($user->organization_id === null) {
                return;
            }

            // 🏢 Normal user → filter by organization
            $builder->where('organization_id', $user->organization_id);
        });
    }
}