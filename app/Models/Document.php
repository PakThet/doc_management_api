<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasBranchGlobalScope;
class Document extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasBranchGlobalScope;

    protected $fillable = [
        'branch_id',
        'document_category_id',
        'document_prefix_id',
        'group_id',
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
        'is_confidential',
        'qr_token',
        'qr_code_path',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'file_size'       => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Document [{$this->document_code}] has been {$eventName}");
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(DocumentGroup::class, 'group_id');
    }
    // ─── Scopes ──────────────────────────────────────────────────────────────────

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('document_code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}