<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Role extends SpatieRole
{
    protected $fillable = [
        'organization_id',
        'name',
        'guard_name',
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