<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\OrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrganizationController extends BaseController
{
    public function index(Request $request)
    {
        $organizations = QueryBuilder::for(Organization::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('status'),
                AllowedFilter::partial('email'),
                AllowedFilter::partial('slug'),
            ])
            ->allowedSorts(['id', 'name', 'slug', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated(
            $organizations,
            OrganizationResource::collection($organizations->items()),
            'Organizations retrieved successfully'
        );
    }

    public function store(OrganizationRequest $request)
    {
        $organization = Organization::create($request->validated());

        return $this->sendResponse(
            new OrganizationResource($organization),
            'Organization created successfully',
            201
        );
    }

    public function show(Organization $organization)
    {
        $organization->loadCount(['branches', 'employees', 'users', 'documents']);

        return $this->sendResponse(
            new OrganizationResource($organization),
            'Organization retrieved successfully'
        );
    }

    public function update(OrganizationRequest $request, Organization $organization)
    {
        $organization->update($request->validated());

        return $this->sendResponse(
            new OrganizationResource($organization),
            'Organization updated successfully'
        );
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return $this->sendResponse(null, 'Organization deleted successfully');
    }
}
