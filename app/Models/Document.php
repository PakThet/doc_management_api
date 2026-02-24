<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'branch_id',
        'document_category_id',
        'document_prefix_id',
        'created_by',
        'verification_token',
        'title',
        'document_code',
        'description',
        'expiration_date',
        'verification_status',
        'file_path',
        'file_type',
        'file_size',
        'qr_code_path',
    ];

    protected $dates = ['expiration_date'];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function documentCategory()
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeExpired($query, $value)
    {
        if ($value === 'true') {
            return $query->whereDate('expiration_date', '<', now());
        }

        return $query->where(function ($q) {
            $q->whereNull('expiration_date')
                ->orWhereDate('expiration_date', '>=', now());
        });
    }

    public function prefix()
    {
        return $this->belongsTo(DocumentPrefix::class, 'document_prefix_id');
    }


    
}
