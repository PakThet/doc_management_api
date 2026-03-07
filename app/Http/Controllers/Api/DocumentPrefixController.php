<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\DocumentPrefixRequest;
use App\Http\Resources\DocumentPrefixResource;
use App\Models\DocumentPrefix;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DocumentPrefixController extends BaseController
{
    public function index(Request $request)
    {
        $prefixes = QueryBuilder::for(DocumentPrefix::class)
            ->with('department')
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('prefix'),
                AllowedFilter::exact('department_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('is_default'),
            ])
            ->allowedSorts(['id', 'name', 'prefix', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated(
            $prefixes,
            DocumentPrefixResource::collection($prefixes->items()),
            'Document prefixes retrieved successfully'
        );
    }

    public function store(DocumentPrefixRequest $request)
    {
        $prefix = DocumentPrefix::create($request->validated());

        return $this->sendResponse(new DocumentPrefixResource($prefix), 'Document prefix created successfully', 201);
    }

    public function show(DocumentPrefix $documentPrefix)
    {
        $documentPrefix->load(['department'])->loadCount('documents');

        return $this->sendResponse(new DocumentPrefixResource($documentPrefix), 'Document prefix retrieved successfully');
    }

    public function update(DocumentPrefixRequest $request, DocumentPrefix $documentPrefix)
    {
        $documentPrefix->update($request->validated());

        return $this->sendResponse(new DocumentPrefixResource($documentPrefix), 'Document prefix updated successfully');
    }

    public function destroy(DocumentPrefix $documentPrefix)
    {
        $documentPrefix->delete();

        return $this->sendResponse(null, 'Document prefix deleted successfully');
    }
}
