<?php
// app/Models/Document.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasOrganizationScope;
class Document extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'employee_id',
        'document_category_id',
        'document_prefix_id',
        'created_by',
        'updated_by',
        'document_code',
        'verification_token',
        'title',
        'description',
        'expiration_date',
        'status',
        'visibility',
        'file_name',
        'file_type',
        'file_size',
        'mime_type',
        'file_path',
        'qr_token',
        'qr_code_path',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'file_size' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'document_code', 'status', 'visibility', 'expiration_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function prefix()
    {
        return $this->belongsTo(DocumentPrefix::class, 'document_prefix_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'published');
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

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id',
        'employee_id', $branchId);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', now())
            ->where('status', '!=', 'expired');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereBetween('expiration_date', [now(), now()->addDays($days)]);
    }
}

