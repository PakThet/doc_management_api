<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'created_by',
        'updated_by',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class, 'group_id');
    }
}