<?php
// app/Http/Controllers/Api/DocumentCategoryController.php

namespace App\Http\Controllers\Api;

use App\Models\DocumentCategory;
use App\Http\Requests\Api\DocumentCategoryRequest;
use App\Http\Resources\DocumentCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentCategoryController extends BaseController
{
    /**
     * Display a listing of document categories
     */
    public function index(Request $request)
    {
        $categories = DocumentCategory::with('organization')
            ->byOrganization($this->getOrganizationId())
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->is_system, function ($query, $isSystem) {
                $query->where('is_system', $isSystem);
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_direction ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated(DocumentCategoryResource::collection($categories), 'Document categories retrieved successfully');
    }

    /**
     * Store a newly created document category
     */
    public function store(DocumentCategoryRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $this->getOrganizationId();
        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();

        $category = DocumentCategory::create($data);

        return $this->sendResponse(new DocumentCategoryResource($category), 'Document category created successfully', 201);
    }

    /**
     * Display the specified document category
     */
    public function show(DocumentCategory $documentCategory)
    {
        return $this->sendResponse(
            new DocumentCategoryResource($documentCategory->load(['organization', 'documents'])),
            'Document category retrieved successfully'
        );
    }

    /**
     * Update the specified document category
     */
    public function update(DocumentCategoryRequest $request, DocumentCategory $documentCategory)
    {
        // Prevent updating system categories name/slug
        if ($documentCategory->is_system && ($request->has('name') || $request->has('slug'))) {
            return $this->sendError('System categories cannot be renamed', [], 422);
        }

        $documentCategory->update($request->validated());

        return $this->sendResponse(new DocumentCategoryResource($documentCategory), 'Document category updated successfully');
    }

    /**
     * Remove the specified document category
     */
    public function destroy(DocumentCategory $documentCategory)
    {
        if ($documentCategory->is_system) {
            return $this->sendError('System categories cannot be deleted', [], 422);
        }

        // Check if category has documents
        if ($documentCategory->documents()->count() > 0) {
            return $this->sendError('Cannot delete category with associated documents', [], 422);
        }

        $documentCategory->delete();

        return $this->sendResponse(null, 'Document category deleted successfully');
    }
}