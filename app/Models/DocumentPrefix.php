<?php
// app/Models/DocumentPrefix.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasOrganizationScope;
class DocumentPrefix extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'prefix',
        'separator',
        'format',
        'description',
        'status',
        'is_default',
        'metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'prefix', 'format', 'status', 'is_default'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
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

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
