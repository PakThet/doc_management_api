<?php
// app/Http/Middleware/SetOrganizationContext.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetOrganizationContext
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if ($user && !$user->isSuperAdmin()) {
            // Set organization context for non-super admins
            if ($user->organization_id) {
                // You can set this in session or use a service
                session(['current_organization_id' => $user->organization_id]);
                
                // Or use a helper to scope queries
                app()->instance('current_organization', $user->organization);
            }
        }

        return $next($request);
    }
}