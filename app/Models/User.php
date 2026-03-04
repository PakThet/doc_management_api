<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasOrganizationScope;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity, HasOrganizationScope;

    protected $guard_name = 'api';


    protected $fillable = [
        'organization_id',
        'first_name',
        'last_name',
        'image',
        'phone',
        'bio',
        'status',
        'email',
        'email_verified_at',
        'two_factor_enabled',
        'password_changed_at',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
    ];

    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'status', 'phone'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function createdDocuments()
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function updatedDocuments()
    {
        return $this->hasMany(Document::class, 'updated_by');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}