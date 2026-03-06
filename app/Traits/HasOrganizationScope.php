<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasOrganizationScope
{
    protected static function bootHasOrganizationScope()
    {
        // Apply global scope
        static::addGlobalScope('organization', function (Builder $builder) {

            $user = Auth::user();

            if (!$user) {
                return;
            }
            /** @var User|null $user */
            // Super Admin sees everything
            if ($user->hasRole('Super Admin')) {
                return;
            }

            $builder->where(
                $builder->getModel()->getTable() . '.organization_id',
                $user->organization_id
            );
        });

        // Auto set organization_id when creating
        static::creating(function ($model) {
            /** @var User|null $user */
            $user = Auth::user();

            if ($user && !$user->hasRole('Super Admin')) {
                $model->organization_id = $user->organization_id;
            }
        });
    }
}