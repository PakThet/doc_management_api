<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\DocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DocumentController extends BaseController
{
    public function index(Request $request)
    {
        $documents = QueryBuilder::for(Document::class)
            ->with(['branch', 'category', 'prefix', 'createdBy', 'updatedBy'])
            ->allowedFilters([
                AllowedFilter::partial('document_code'),
                AllowedFilter::partial('title'),
                AllowedFilter::exact('branch_id'),
                AllowedFilter::exact('document_category_id'),
                AllowedFilter::exact('document_prefix_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('visibility'),
            ])
            ->allowedSorts(['id', 'document_code', 'title', 'expiration_date', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated(
            $documents,
            DocumentResource::collection($documents->items()),
            'Documents retrieved successfully'
        );
    }

    public function store(DocumentRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $document = Document::create($data);

        return $this->sendResponse(new DocumentResource($document), 'Document created successfully', 201);
    }

    public function show(Document $document)
    {
        $document->load(['branch', 'category', 'prefix', 'createdBy', 'updatedBy']);

        return $this->sendResponse(new DocumentResource($document), 'Document retrieved successfully');
    }

    public function update(DocumentRequest $request, Document $document)
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $document->update($data);

        return $this->sendResponse(new DocumentResource($document), 'Document updated successfully');
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return $this->sendResponse(null, 'Document deleted successfully');
    }

    public function verify(string $token)
    {
        $document = Document::query()
            ->with(['branch', 'category', 'prefix'])
            ->where('verification_token', $token)
            ->first();

        if (! $document) {
            return $this->sendError('Invalid verification token', [], 404);
        }

        return $this->sendResponse(new DocumentResource($document), 'Document verified successfully');
    }
}
