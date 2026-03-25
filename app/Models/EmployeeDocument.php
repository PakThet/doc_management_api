<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'file_name',
        'file_type',
        'file_size',
        'mime_type',
        'file_path',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'file_size'   => 'integer',
    ];

    // Append file_url so it appears in all JSON responses
    protected $appends = ['file_url'];

    // ─── Accessors ───────────────────────────────────────────────────────────────

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}