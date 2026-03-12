<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'achievement_date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}