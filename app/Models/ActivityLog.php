<?php
// app/Models/ActivityLog.php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class ActivityLog extends SpatieActivity
{
    protected $table = 'activity_log';

    // You can add custom methods or relationships here
    // For example, if you want to add organization scope
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->whereHas('causer', function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        });
    }
}