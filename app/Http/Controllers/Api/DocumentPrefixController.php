<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentPrefix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DocumentPrefixController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view document-prefixes')->only(['index', 'show']);
        $this->middleware('permission:create document-prefixes')->only(['store']);
        $this->middleware('permission:edit document-prefixes')->only(['update']);
        $this->middleware('permission:delete document-prefixes')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        $prefixes = QueryBuilder::for(DocumentPrefix::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('is_default'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('prefix'),
            ])
            ->allowedSorts(['name', 'prefix', 'created_at', 'status'])
            ->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($prefixes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'prefix'       => 'required|string|max:20|unique:document_prefixes,prefix',
            'separator'    => 'nullable|string|max:5',
            'format'       => 'required|string',
            'description'  => 'nullable|string',
            'status'       => 'nullable|in:active,inactive',
            'is_default'   => 'nullable|boolean',
            'reset_period' => 'nullable|in:year,month,day,never',
        ]);

        if (! empty($validated['is_default'])) {
            DocumentPrefix::where('is_default', true)->update(['is_default' => false]);
        }

        $validated['status']           ??= 'active';
        $validated['is_default']       ??= false;
        $validated['current_sequence'] = 0;

        $prefix = DocumentPrefix::create($validated);

        return response()->json($prefix->fresh(), 201);
    }

    public function show(DocumentPrefix $documentPrefix): JsonResponse
    {
        return response()->json($documentPrefix);
    }

    public function update(Request $request, DocumentPrefix $documentPrefix): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'prefix'       => "sometimes|string|max:20|unique:document_prefixes,prefix,{$documentPrefix->id}",
            'separator'    => 'nullable|string|max:5',
            'format'       => 'sometimes|string',
            'description'  => 'nullable|string',
            'status'       => 'nullable|in:active,inactive',
            'is_default'   => 'nullable|boolean',
            'reset_period' => 'nullable|in:year,month,day,never',
        ]);

        if (! empty($validated['is_default'])) {
            DocumentPrefix::where('id', '!=', $documentPrefix->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $documentPrefix->update($validated);

        return response()->json($documentPrefix->fresh());
    }

    public function destroy(DocumentPrefix $documentPrefix): JsonResponse
    {
        $documentPrefix->delete();

        return response()->json([
            'message' => 'Document prefix deleted successfully.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $prefix = DocumentPrefix::withTrashed()->findOrFail($id);
        $prefix->restore();

        return response()->json([
            'message' => 'Document prefix restored successfully.',
            'data'    => $prefix->fresh(),
        ]);
    }

    public function generate(DocumentPrefix $documentPrefix): JsonResponse
    {
        if ($documentPrefix->status !== 'active') {
            return response()->json([
                'message' => 'Cannot generate a number for an inactive prefix.',
            ], 422);
        }

        $number = $documentPrefix->generateNumber();

        return response()->json([
            'document_number' => $number,
        ]);
    }
}