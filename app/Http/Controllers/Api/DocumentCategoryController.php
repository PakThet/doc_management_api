<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\DocumentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DocumentCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view document-categories')->only(['index', 'show']);
        $this->middleware('permission:create document-categories')->only(['store']);
        $this->middleware('permission:edit document-categories')->only(['update']);
        $this->middleware('permission:delete document-categories')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        $categories = QueryBuilder::for(DocumentCategory::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts(['name', 'created_at', 'status'])
            ->allowedIncludes(['documents'])
            ->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|unique:document_categories,slug',
            'description' => 'nullable|string',
            'status'      => 'in:active,inactive',
        ]);

        $category = DocumentCategory::create($validated);

        return response()->json($category, 201);
    }

    public function show(DocumentCategory $documentCategory): JsonResponse
    {
        $documentCategory->load(['documents']);

        return response()->json($documentCategory);
    }

    public function update(Request $request, DocumentCategory $documentCategory): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'slug'        => "sometimes|string|unique:document_categories,slug,{$documentCategory->id}",
            'description' => 'nullable|string',
            'status'      => 'in:active,inactive',
        ]);

        $documentCategory->update($validated);

        return response()->json($documentCategory);
    }

    public function destroy(DocumentCategory $documentCategory): JsonResponse
    {
        $documentCategory->delete();

        return response()->json(['message' => 'Document category deleted successfully.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete document-categories');

        $category = DocumentCategory::withTrashed()->findOrFail($id);
        $category->restore();

        return response()->json(['message' => 'Document category restored successfully.']);
    }
}