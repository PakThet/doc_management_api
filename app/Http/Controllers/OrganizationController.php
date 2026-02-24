<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Http\Requests\OrganizationRequest;
use App\Http\Resources\OrganizationResource;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = QueryBuilder::for(Organization::class)
            ->with('branches')
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('type'),
                AllowedFilter::exact('email'),
            ])
            ->allowedSorts([
                'name',
                'type',
                'created_at'
            ])
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return OrganizationResource::collection($organizations);
    }

    public function store(OrganizationRequest $request)
    {
        $data = $request->validated();

        // Upload logo
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')
                ->store('logos', 'public');
        }

        $organization = Organization::create($data);

        return response()->json([
            'message' => 'Organization created successfully',
            'data' => new OrganizationResource($organization)
        ], 201);
    }

    public function show(Organization $organization)
    {
        $organization->load('branches');

        return new OrganizationResource($organization);
    }


    public function update(OrganizationRequest $request, Organization $organization)
    {
        $data = $request->validated();

        // Update logo
        if ($request->hasFile('logo')) {

            // Delete old logo
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }

            $data['logo'] = $request->file('logo')
                ->store('logos', 'public');
        }

        $organization->update($data);

        return response()->json([
            'message' => 'Organization updated successfully',
            'data' => new OrganizationResource($organization)
        ]);
    }


    public function destroy(Organization $organization)
    {
        // Delete logo file
        if ($organization->logo) {
            Storage::disk('public')->delete($organization->logo);
        }

        $organization->delete(); // soft delete

        return response()->json([
            'message' => 'Organization deleted successfully'
        ]);
    }

}