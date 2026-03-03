<?php
// app/Http/Controllers/Api/OrganizationController.php

namespace App\Http\Controllers\Api;

use App\Models\Organization;
use App\Http\Requests\Api\OrganizationRequest;
use App\Http\Resources\OrganizationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends BaseController
{
    /**
     * Display a listing of organizations
     */
    public function index(Request $request)
    {
        $organizations = Organization::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_direction ?? 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated(OrganizationResource::collection($organizations), 'Organizations retrieved successfully');
    }

    /**
     * Store a newly created organization
     */
    public function store(OrganizationRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']) . '-' . uniqid();

        $organization = Organization::create($data);

        return $this->sendResponse(new OrganizationResource($organization), 'Organization created successfully', 201);
    }

    /**
     * Display the specified organization
     */
    public function show(Organization $organization)
    {
        return $this->sendResponse(
            new OrganizationResource($organization->load(['branches', 'departments', 'users'])),
            'Organization retrieved successfully'
        );
    }

    /**
     * Update the specified organization
     */
    public function update(OrganizationRequest $request, Organization $organization)
    {
        $organization->update($request->validated());

        return $this->sendResponse(new OrganizationResource($organization), 'Organization updated successfully');
    }

    /**
     * Remove the specified organization
     */
    public function destroy(Organization $organization)
    {
        // Check if organization has related records
        if ($organization->users()->count() > 0) {
            return $this->sendError('Cannot delete organization with associated users', [], 422);
        }

        $organization->delete();

        return $this->sendResponse(null, 'Organization deleted successfully');
    }

    /**
     * Get organization statistics
     */
    public function statistics(Organization $organization)
    {
        $stats = [
            'total_branches' => $organization->branches()->count(),
            'active_branches' => $organization->branches()->where('status', 'active')->count(),
            'total_departments' => $organization->departments()->count(),
            'active_departments' => $organization->departments()->where('status', 'active')->count(),
            'total_employees' => $organization->employees()->count(),
            'active_employees' => $organization->employees()->where('status', 'active')->count(),
            'total_users' => $organization->users()->count(),
            'active_users' => $organization->users()->where('status', 'active')->count(),
            'total_documents' => $organization->documents()->count(),
            'published_documents' => $organization->documents()->where('status', 'published')->count(),
        ];

        return $this->sendResponse($stats, 'Organization statistics retrieved successfully');
    }
}