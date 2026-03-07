<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DocumentPrefix extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'document_prefixes';

    protected $fillable = [
        'department_id',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'prefix', 'separator', 'format', 'status', 'is_default'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('document_prefix');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'document_prefix_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function generateDocumentNumber($number): string
    {
        $replacements = [
            '{PREFIX}' => $this->prefix,
            '{SEPARATOR}' => $this->separator,
            '{YEAR}' => date('Y'),
            '{MONTH}' => date('m'),
            '{DAY}' => date('d'),
            '{NUMBER}' => str_pad($number, 5, '0', STR_PAD_LEFT),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $this->format);
    }
}