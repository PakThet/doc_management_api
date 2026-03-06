<?php
// app/Models/Department.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasOrganizationScope;
class Department extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'description',
        'parent_id',
        'head_of_department_id',
        'email',
        'phone',
        'location',
        'budget',
        'status',
        'metadata',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'status', 'budget', 'head_of_department_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function headOfDepartment()
    {
        return $this->belongsTo(Employee::class, 'head_of_department_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByOrganization($query, $organizationId)
    {
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return $query;
        }

        if (is_null($organizationId)) {
            return $query;
        }

        return $query->where('organization_id', $organizationId);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
