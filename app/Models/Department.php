<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Department extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'departments';

    protected $fillable = [
        'branch_id',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'email', 'phone', 'status', 'budget'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('department');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function headOfDepartment(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'head_of_department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function documentPrefixes(): HasMany
    {
        return $this->hasMany(DocumentPrefix::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithHead($query)
    {
        return $query->whereNotNull('head_of_department_id');
    }
}