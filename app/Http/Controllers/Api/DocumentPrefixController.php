<?php
// app/Http/Controllers/Api/DocumentPrefixController.php

namespace App\Http\Controllers\Api;

use App\Models\DocumentPrefix;
use App\Http\Requests\Api\DocumentPrefixRequest;
use App\Http\Resources\DocumentPrefixResource;
use Illuminate\Http\Request;

class DocumentPrefixController extends BaseController
{
    /**
     * Display a listing of document prefixes
     */
    public function index(Request $request)
    {
        $prefixes = DocumentPrefix::with('organization')
            ->byOrganization($this->getOrganizationId())
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('prefix', 'like', "%{$search}%")
                    ->orWhere('format', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->is_default, function ($query, $isDefault) {
                $query->where('is_default', $isDefault);
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_direction ?? 'asc')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated(DocumentPrefixResource::collection($prefixes), 'Document prefixes retrieved successfully');
    }

    /**
     * Store a newly created document prefix
     */
    public function store(DocumentPrefixRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $this->getOrganizationId();

        // If this is set as default, unset other defaults
        if (isset($data['is_default']) && $data['is_default']) {
            DocumentPrefix::byOrganization($this->getOrganizationId())
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $prefix = DocumentPrefix::create($data);

        return $this->sendResponse(new DocumentPrefixResource($prefix), 'Document prefix created successfully', 201);
    }

    /**
     * Display the specified document prefix
     */
    public function show(DocumentPrefix $documentPrefix)
    {
        return $this->sendResponse(
            new DocumentPrefixResource($documentPrefix->load(['organization', 'documents'])),
            'Document prefix retrieved successfully'
        );
    }

    /**
     * Update the specified document prefix
     */
    public function update(DocumentPrefixRequest $request, DocumentPrefix $documentPrefix)
    {
        $data = $request->validated();

        // If this is set as default, unset other defaults
        if (isset($data['is_default']) && $data['is_default']) {
            DocumentPrefix::byOrganization($this->getOrganizationId())
                ->where('is_default', true)
                ->where('id', '!=', $documentPrefix->id)
                ->update(['is_default' => false]);
        }

        $documentPrefix->update($data);

        return $this->sendResponse(new DocumentPrefixResource($documentPrefix), 'Document prefix updated successfully');
    }

    /**
     * Remove the specified document prefix
     */
    public function destroy(DocumentPrefix $documentPrefix)
    {
        if ($documentPrefix->is_default) {
            return $this->sendError('Cannot delete default prefix. Please set another prefix as default first.', [], 422);
        }

        // Check if prefix is used by documents
        if ($documentPrefix->documents()->count() > 0) {
            return $this->sendError('Cannot delete prefix that is used by documents', [], 422);
        }

        $documentPrefix->delete();

        return $this->sendResponse(null, 'Document prefix deleted successfully');
    }

    /**
     * Set as default
     */
    public function setDefault(DocumentPrefix $documentPrefix)
    {
        // Unset other defaults
        DocumentPrefix::byOrganization($this->getOrganizationId())
            ->where('is_default', true)
            ->where('id', '!=', $documentPrefix->id)
            ->update(['is_default' => false]);

        $documentPrefix->update(['is_default' => true]);

        return $this->sendResponse(new DocumentPrefixResource($documentPrefix), 'Document prefix set as default successfully');
    }

    /**
     * Generate next document code
     */
    public function generateNextCode(DocumentPrefix $documentPrefix)
    {
        $lastDocument = $documentPrefix->documents()
            ->whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastDocument ? intval(substr($lastDocument->document_code, -5)) + 1 : 1;

        $code = $documentPrefix->format;
        $code = str_replace('{prefix}', $documentPrefix->prefix, $code);
        $code = str_replace('{separator}', $documentPrefix->separator, $code);
        $code = str_replace('{year}', now()->format('Y'), $code);
        $code = str_replace('{month}', now()->format('m'), $code);
        $code = str_replace('{day}', now()->format('d'), $code);
        $code = str_replace('{number}', str_pad($nextNumber, 5, '0', STR_PAD_LEFT), $code);

        return $this->sendResponse([
            'next_code' => $code,
            'next_number' => $nextNumber
        ], 'Next document code generated successfully');
    }
}