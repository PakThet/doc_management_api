<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'organization_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('organization_id', Auth::user()->organization_id);
            }
        });
    }

}