<?php
// app/Http/Controllers/Api/DocumentController.php

namespace App\Http\Controllers\Api;

use App\Models\Document;
use App\Models\DocumentPrefix;
use App\Http\Requests\Api\DocumentRequest;
use App\Http\Resources\DocumentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentController extends BaseController
{
    /**
     * Display a listing of documents
     */
    public function index(Request $request)
    {
        $documents = Document::with(['organization', 'branch', 'category', 'prefix', 'creator'])
            ->byOrganization($this->getOrganizationId())
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('document_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('document_category_id', $categoryId);
            })
            ->when($request->visibility, function ($query, $visibility) {
                $query->where('visibility', $visibility);
            })
            ->when($request->expired, function ($query) {
                $query->expired();
            })
            ->when($request->expiring_soon, function ($query) use ($request) {
                $days = $request->expiring_soon_days ?? 30;
                $query->expiringSoon($days);
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_direction ?? 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->sendPaginated(DocumentResource::collection($documents), 'Documents retrieved successfully');
    }

    /**
     * Store a newly created document
     */
    public function store(DocumentRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $this->getOrganizationId();
        $data['created_by'] = Auth::id();
        $data['verification_token'] = Str::random(64);
        $data['qr_token'] = Str::random(32);

        // Generate document code if prefix is provided
        if (isset($data['document_prefix_id'])) {
            $prefix = DocumentPrefix::find($data['document_prefix_id']);
            if ($prefix) {
                $lastDocument = Document::where('document_prefix_id', $prefix->id)
                    ->whereYear('created_at', now()->year)
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNumber = $lastDocument ? intval(substr($lastDocument->document_code, -5)) + 1 : 1;

                $code = $prefix->format;
                $code = str_replace('{prefix}', $prefix->prefix, $code);
                $code = str_replace('{separator}', $prefix->separator, $code);
                $code = str_replace('{year}', now()->format('Y'), $code);
                $code = str_replace('{month}', now()->format('m'), $code);
                $code = str_replace('{day}', now()->format('d'), $code);
                $code = str_replace('{number}', str_pad($nextNumber, 5, '0', STR_PAD_LEFT), $code);

                $data['document_code'] = $code;
            }
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents/' . now()->format('Y/m'), 'public');

            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
            $data['file_path'] = $path;
        }

        // Generate QR code
        $qrCodePath = 'qrcodes/' . $data['qr_token'] . '.png';
        $qrCode = QrCode::format('png')
            ->size(200)
            ->generate(route('documents.verify', $data['qr_token']));
        Storage::disk('public')->put($qrCodePath, $qrCode);
        $data['qr_code_path'] = $qrCodePath;

        $document = Document::create($data);

        return $this->sendResponse(new DocumentResource($document), 'Document created successfully', 201);
    }

    /**
     * Display the specified document
     */
    public function show(Document $document)
    {
        return $this->sendResponse(
            new DocumentResource($document->load(['organization', 'branch', 'category', 'prefix', 'creator', 'updater'])),
            'Document retrieved successfully'
        );
    }

    /**
     * Update the specified document
     */
    public function update(DocumentRequest $request, Document $document)
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $path = $file->store('documents/' . now()->format('Y/m'), 'public');

            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
            $data['file_path'] = $path;
        }

        $document->update($data);

        return $this->sendResponse(new DocumentResource($document), 'Document updated successfully');
    }

    /**
     * Remove the specified document
     */
    public function destroy(Document $document)
    {
        // Delete file
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete QR code
        if ($document->qr_code_path) {
            Storage::disk('public')->delete($document->qr_code_path);
        }

        $document->delete();

        return $this->sendResponse(null, 'Document deleted successfully');
    }

    /**
     * Download document
     */
    public function download(Document $document)
    {
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return $this->sendError('File not found', [], 404);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->download($document->file_path, $document->file_name);
    }

    /**
     * Verify document by token
     */
    public function verify($token)
    {
        $document = Document::where('verification_token', $token)
            ->orWhere('qr_token', $token)
            ->first();

        if (!$document) {
            return $this->sendError('Invalid verification token', [], 404);
        }

        return $this->sendResponse([
            'document' => new DocumentResource($document),
            'verified_at' => now(),
            'is_valid' => !$document->expiration_date || $document->expiration_date->isFuture(),
            'message' => 'Document verified successfully'
        ], 'Document verification successful');
    }

    /**
     * Publish document
     */
    public function publish(Document $document)
    {
        $document->update([
            'status' => 'published',
            'updated_by' => Auth::id()
        ]);

        return $this->sendResponse(new DocumentResource($document), 'Document published successfully');
    }

    /**
     * Archive document
     */
    public function archive(Document $document)
    {
        $document->update([
            'status' => 'archived',
            'updated_by' => Auth::id()
        ]);

        return $this->sendResponse(new DocumentResource($document), 'Document archived successfully');
    }

    /**
     * Get document statistics
     */
    public function statistics()
    {
        $organizationId = $this->getOrganizationId();

        $stats = [
            'total' => Document::byOrganization($organizationId)->count(),
            'draft' => Document::byOrganization($organizationId)->where('status', 'draft')->count(),
            'published' => Document::byOrganization($organizationId)->where('status', 'published')->count(),
            'archived' => Document::byOrganization($organizationId)->where('status', 'archived')->count(),
            'expired' => Document::byOrganization($organizationId)->expired()->count(),
            'expiring_soon' => Document::byOrganization($organizationId)->expiringSoon(30)->count(),
            'by_category' => Document::byOrganization($organizationId)
                ->selectRaw('document_category_id, count(*) as count')
                ->with('category:id,name')
                ->groupBy('document_category_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => $item->category->name ?? 'Uncategorized',
                        'count' => $item->count
                    ];
                }),
            'by_branch' => Document::byOrganization($organizationId)
                ->selectRaw('branch_id, count(*) as count')
                ->with('branch:id,name')
                ->groupBy('branch_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'branch' => $item->branch->name ?? 'Unknown',
                        'count' => $item->count
                    ];
                }),
        ];

        return $this->sendResponse($stats, 'Document statistics retrieved successfully');
    }
}
