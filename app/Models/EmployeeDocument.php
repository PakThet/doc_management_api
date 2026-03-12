<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'file_name',
        'file_type',
        'file_size',
        'mime_type',
        'file_path',
        'expiry_date'
    ];

    protected $casts = [
        'expiry_date' => 'date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}