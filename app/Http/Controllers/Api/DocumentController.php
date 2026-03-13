<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['verify']);

        $this->middleware('permission:view documents')->only(['index', 'show']);
        $this->middleware('permission:create documents')->only(['store']);
        $this->middleware('permission:edit documents')->only(['update', 'move']);
        $this->middleware('permission:delete documents')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        $documents = QueryBuilder::for(Document::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('visibility'),
                AllowedFilter::exact('branch_id'),
                AllowedFilter::exact('group_id'),
                AllowedFilter::partial('title'),
                AllowedFilter::partial('document_code'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['title', 'document_code', 'created_at', 'expiration_date'])
            ->allowedIncludes(['branch', 'category', 'prefix', 'creator', 'updater', 'group'])
            ->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        $documents->getCollection()->transform(function ($doc) {
            $doc->file_url = $doc->file_path ? url(Storage::url($doc->file_path)) : null;
            $doc->qr_code_url = $doc->qr_code_path ? url(Storage::url($doc->qr_code_path)) : null;
            return $doc;
        });

        return response()->json($documents);
    }

    public function store(Request $request, $groupId = null): JsonResponse
    {
        $this->authorize('create', Document::class);
        $user = Auth::user();

        $validated = $request->validate([
            'document_category_id' => 'nullable|exists:document_categories,id',
            'document_prefix_id' => 'nullable|exists:document_prefixes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:draft,published,archived,expired',
            'visibility' => 'in:public,private,restricted',
            'expiration_date' => 'nullable|date',
            'is_confidential' => 'boolean'
        ]);

        $validated['branch_id'] = $user->branch_id;
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();
        $validated['document_code'] = strtoupper(Str::random(10));
        if ($groupId) {
            $validated['group_id'] = $groupId;
        } elseif ($request->has('group_id')) {
            $validated['group_id'] = $request->group_id;
        }
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = $file->getSize();
            $validated['mime_type'] = $file->getMimeType();
            $validated['file_path'] = $path;
        }

        if (!empty($validated['is_confidential'])) {
            $token = Str::uuid();
            $validated['verification_token'] = $token;
            $validated['qr_token'] = $token;

            $url = url("/api/documents/verify/{$token}");
            $qrImage = QrCode::size(300)->generate($url);
            $qrPath = 'qrcodes/' . $token . '.svg';
            Storage::disk('public')->put($qrPath, $qrImage);
            $validated['qr_code_path'] = $qrPath;
        }

        $document = Document::create($validated);

        return response()->json($document->load('group'), 201);
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $document->load(['branch', 'category', 'prefix', 'creator', 'updater', 'group']);
        $document->file_url = $document->file_path ? url(Storage::url($document->file_path)) : null;
        $document->qr_code_url = $document->qr_code_path ? url(Storage::url($document->qr_code_path)) : null;

        return response()->json($document);
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'group_id' => 'nullable|exists:document_groups,id',
            'document_category_id' => 'nullable|exists:document_categories,id',
            'document_prefix_id' => 'nullable|exists:document_prefixes,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:draft,published,archived,expired',
            'visibility' => 'sometimes|in:public,private,restricted',
            'expiration_date' => 'nullable|date|after_or_equal:today',
            'file' => 'nullable|file|max:10240',
            'is_confidential' => 'nullable|boolean',
        ]);

        $validated['updated_by'] = Auth::id();

        // Handle confidential documents
        if (isset($validated['is_confidential'])) {
            if ($validated['is_confidential'] && !$document->verification_token) {
                $token = Str::uuid();
                $validated['verification_token'] = $token;
                $validated['qr_token'] = $token;

                $url = url("/api/documents/verify/{$token}");
                $qrImage = QrCode::size(300)->generate($url);
                $qrPath = "qrcodes/{$token}.svg";
                Storage::disk('public')->put($qrPath, $qrImage);
                $validated['qr_code_path'] = $qrPath;
            }

            if (!$validated['is_confidential'] && $document->qr_code_path) {
                Storage::disk('public')->delete($document->qr_code_path);
                $validated['verification_token'] = null;
                $validated['qr_token'] = null;
                $validated['qr_code_path'] = null;
            }
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = $file->getSize();
            $validated['mime_type'] = $file->getMimeType();
            $validated['file_path'] = $path;
        }

        $document->update($validated);

        return response()->json($document->load('group'));
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        if ($document->qr_code_path && Storage::disk('public')->exists($document->qr_code_path)) {
            Storage::disk('public')->delete($document->qr_code_path);
        }

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    public function restore($id): JsonResponse
    {
        $document = Document::withTrashed()->findOrFail($id);
        $this->authorize('restore', $document);
        $document->restore();

        return response()->json(['message' => 'Document restored successfully']);
    }

    public function verify(string $token): JsonResponse
    {
        $document = Document::where('verification_token', $token)
            ->orWhere('qr_token', $token)
            ->firstOrFail();

        return response()->json([
            'document_code' => $document->document_code,
            'title' => $document->title,
            'status' => $document->status,
            'issued_by' => $document->branch->name ?? null,
            'expiration' => $document->expiration_date,
        ]);
    }

    // Move document to another group
    public function move(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'group_id' => 'nullable|exists:document_groups,id'
        ]);

        $document->group_id = $validated['group_id'] ?? null;
        $document->updated_by = Auth::id();
        $document->save();

        return response()->json([
            'message' => 'Document moved successfully',
            'document' => $document
        ]);
    }
}
