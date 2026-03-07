<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'documents';

    protected $fillable = [
        'branch_id',
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
        'expiration_date' => 'date',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $document): void {
            if (empty($document->verification_token)) {
                $document->verification_token = Str::random(64);
            }

            if (empty($document->qr_token)) {
                $document->qr_token = (string) Str::uuid();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['document_code', 'title', 'status', 'visibility', 'expiration_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('document');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function prefix(): BelongsTo
    {
        return $this->belongsTo(DocumentPrefix::class, 'document_prefix_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function creator(): BelongsTo
    {
        return $this->createdBy();
    }

    public function updater(): BelongsTo
    {
        return $this->updatedBy();
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->qr_code_path ? asset('storage/' . $this->qr_code_path) : null;
    }

    public function getVerificationUrlAttribute(): string
    {
        return route('documents.verify', $this->verification_token);
    }

    public function getIsExpiredAttribute(): bool
    {
        return (bool) ($this->expiration_date && $this->expiration_date->isPast());
    }

    public function getFileSizeForHumansAttribute(): string
    {
        $bytes = max(0, (int) $this->file_size);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('expiration_date')
                    ->where('expiration_date', '<', now());
            });
    }

    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiration_date')
                ->orWhere('expiration_date', '>=', now());
        })->where('status', 'published');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    public function scopeRestricted($query)
    {
        return $query->where('visibility', 'restricted');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('document_category_id', $categoryId);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}
