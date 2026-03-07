<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Branch extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'branches';

    protected $fillable = [
        'organization_id',
        'name',
        'address',
        'phone',
        'email',
        'code',
        'city',
        'state',
        'country',
        'postal_code',
        'established_date',
        'status',
        'settings',
    ];

    protected $casts = [
        'established_date' => 'date',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'email', 'phone', 'status', 'city'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('branch');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }
}