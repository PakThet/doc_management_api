<?php

namespace App\Http\Controllers;

use App\Models\DocumentCategory;
use App\Http\Requests\DocumentCategoryRequest;
use App\Http\Resources\DocumentCategoryResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class DocumentCategoryController extends Controller
{
    public function index()
    {
        $categories = QueryBuilder::for(DocumentCategory::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts([
                'name',
                'created_at'
            ])
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return DocumentCategoryResource::collection($categories);
    }

    public function store(DocumentCategoryRequest $request)
    {
        $category = DocumentCategory::create($request->validated());

        return response()->json([
            'message' => 'Category created successfully',
            'data' => new DocumentCategoryResource($category)
        ], 201);
    }

    public function show(DocumentCategory $documentCategory)
    {
        return new DocumentCategoryResource($documentCategory);
    }

    public function update(DocumentCategoryRequest $request, DocumentCategory $documentCategory)
    {
        $documentCategory->update($request->validated());

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => new DocumentCategoryResource($documentCategory)
        ]);
    }

    public function destroy(DocumentCategory $documentCategory)
    {
        $documentCategory->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}