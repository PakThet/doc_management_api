<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Organization extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo',
        'website',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'status', 'phone', 'website'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('organization');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function employees(): HasManyThrough
    {
        return $this->hasManyThrough(Employee::class, Branch::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Branch::class);
    }

    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(Document::class, Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
