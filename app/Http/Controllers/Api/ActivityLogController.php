<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view activity-logs');
    }

    public function index(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = QueryBuilder::for(Activity::class)
            ->allowedFilters([
                AllowedFilter::exact('log_name'),
                AllowedFilter::exact('event'),
                AllowedFilter::exact('subject_type'),
                AllowedFilter::exact('causer_type'),
                AllowedFilter::exact('causer_id'),
            ])
            ->allowedSorts(['created_at', 'log_name', 'event'])
            ->allowedIncludes(['causer', 'subject'])
            ->latest();

        // Non-super-admins see only their own branch's activity
        if (! $user->hasRole('super-admin') && $user->branch_id) {
            // Filter activity to causer in the same branch
            $query->whereHasMorph('causer', [\App\Models\User::class], function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        $logs = $query->paginate(request()->integer('per_page', 25))
            ->appends(request()->query());

        return response()->json($logs);
    }

    public function show(Activity $activity): JsonResponse
    {
        return response()->json($activity->load(['causer', 'subject']));
    }

    /**
     * Causer's own activity.
     */
    public function myActivity(): JsonResponse
    {
        $logs = Activity::causedBy(Auth::user())
            ->latest()
            ->paginate(request()->integer('per_page', 25));

        return response()->json($logs);
    }

    public function destroy(int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Optional: only allow super-admins
        if (! $user->hasRole('super-admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $activity = Activity::find($id);
        if (! $activity) {
            return response()->json(['message' => 'Activity not found'], 404);
        }

        $activity->delete();

        return response()->json(['message' => 'Activity deleted']);
    }
}
