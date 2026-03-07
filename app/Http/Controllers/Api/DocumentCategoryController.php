<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\DocumentCategoryRequest;
use App\Http\Resources\DocumentCategoryResource;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DocumentCategoryController extends BaseController
{
    public function index(Request $request)
    {
        $categories = QueryBuilder::for(DocumentCategory::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('slug'),
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['id', 'name', 'slug', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated(
            $categories,
            DocumentCategoryResource::collection($categories->items()),
            'Document categories retrieved successfully'
        );
    }

    public function store(DocumentCategoryRequest $request)
    {
        $category = DocumentCategory::create($request->validated());

        return $this->sendResponse(new DocumentCategoryResource($category), 'Document category created successfully', 201);
    }

    public function show(DocumentCategory $documentCategory)
    {
        $documentCategory->loadCount('documents');

        return $this->sendResponse(new DocumentCategoryResource($documentCategory), 'Document category retrieved successfully');
    }

    public function update(DocumentCategoryRequest $request, DocumentCategory $documentCategory)
    {
        $documentCategory->update($request->validated());

        return $this->sendResponse(new DocumentCategoryResource($documentCategory), 'Document category updated successfully');
    }

    public function destroy(DocumentCategory $documentCategory)
    {
        $documentCategory->delete();

        return $this->sendResponse(null, 'Document category deleted successfully');
    }
}
