<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentPrefix extends Model
{
    protected $fillable = [
        'name',
        'prefix',
        'format_pattern',
        'is_active'
    ];

}
