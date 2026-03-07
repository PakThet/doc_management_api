<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'employees';

    protected $fillable = [
        'branch_id',
        'department_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'status',
        'employment_type',
        'date_of_birth',
        'join_date',
        'probation_end_date',
        'confirmation_date',
        'resignation_date',
        'exit_date',
        'position',
        'salary',
        'emergency_contact_name',
        'emergency_contact_phone',
        'bank_details',
        'documents',
        'metadata',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'probation_end_date' => 'date',
        'confirmation_date' => 'date',
        'resignation_date' => 'date',
        'exit_date' => 'date',
        'salary' => 'decimal:2',
        'bank_details' => 'array',
        'documents' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['employee_code', 'first_name', 'last_name', 'email', 'phone', 'status', 'position', 'salary'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('employee');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function headedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'head_of_department_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getYearsOfServiceAttribute(): ?int
    {
        return $this->join_date?->diffInYears(now());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    public function scopeOnLeave($query)
    {
        return $query->where('status', 'on_leave');
    }

    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }

    public function scopeJoinDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('join_date', [$startDate, $endDate]);
    }
}