<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasBranchGlobalScope;
class Employee extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasBranchGlobalScope;

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
        'date_of_birth'       => 'date',
        'join_date'           => 'date',
        'probation_end_date'  => 'date',
        'confirmation_date'   => 'date',
        'resignation_date'    => 'date',
        'exit_date'           => 'date',
        'salary'              => 'decimal:2',
        'bank_details'        => 'array',
        'documents'           => 'array',
        'metadata'            => 'array',
    ];

    protected $hidden = ['bank_details'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->setDescriptionForEvent(fn(string $eventName) => "Employee [{$this->full_name}] has been {$eventName}");
    }

    // ─── Accessors ───────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'head_of_department_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────────

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('employee_code', 'like', "%{$search}%");
        });
    }
}