<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view organizations')->only(['index', 'show']);
        $this->middleware('permission:create organizations')->only(['store']);
        $this->middleware('permission:edit organizations')->only(['update']);
        $this->middleware('permission:delete organizations')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $organizations = QueryBuilder::for(Organization::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
            ])
            ->allowedSorts(['name', 'created_at', 'status'])
            ->allowedIncludes(['branches'])
            ->withTrashed(request()->boolean('with_trashed') && $user?->hasRole('super-admin'))
            ->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($organizations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'slug'     => 'required|string|unique:organizations,slug',
            'email'    => 'required|email|unique:organizations,email',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'logo'     => 'nullable|string',
            'website'  => 'nullable|url',
            'status'   => 'in:active,inactive,suspended',
            'settings' => 'nullable|array',
        ]);

        $organization = Organization::create($validated);

        return response()->json($organization, 201);
    }

    public function show(Organization $organization): JsonResponse
    {
        $organization->load(['branches']);

        return response()->json($organization);
    }

    public function update(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'slug'     => "sometimes|string|unique:organizations,slug,{$organization->id}",
            'email'    => "sometimes|email|unique:organizations,email,{$organization->id}",
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'logo'     => 'nullable|string',
            'website'  => 'nullable|url',
            'status'   => 'in:active,inactive,suspended',
            'settings' => 'nullable|array',
        ]);

        $organization->update($validated);

        return response()->json($organization);
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete organizations');

        $organization = Organization::withTrashed()->findOrFail($id);
        $organization->restore();

        return response()->json(['message' => 'Organization restored successfully.']);
    }
}
