<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_title',
        'department_id',
        'level',
        'created_by',
    ];


    // Position belongs to a Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Position has many Employees
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    // User who created the position
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}