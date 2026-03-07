<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Role extends SpatieRole
{
    use SoftDeletes, LogsActivity;

    protected $attributes = [
        'guard_name' => 'api',
    ];

    protected $fillable = [
        'branch_id',
        'name',
        'guard_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'branch_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('role');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('branch_id');
    }
}
