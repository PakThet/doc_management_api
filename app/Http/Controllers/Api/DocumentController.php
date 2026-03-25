<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentPrefix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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
            $doc->file_url    = $doc->file_path    ? url(Storage::url($doc->file_path))    : null;
            $doc->qr_code_url = $doc->qr_code_path ? url(Storage::url($doc->qr_code_path)) : null;
            return $doc;
        });

        return response()->json($documents);
    }

    public function store(Request $request, $groupId = null): JsonResponse
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'document_category_id' => 'nullable|exists:document_categories,id',
            'document_prefix_id'   => 'nullable|exists:document_prefixes,id',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'status'               => 'nullable|in:draft,published,archived,expired',
            'visibility'           => 'nullable|in:public,private,restricted',
            'expiration_date'      => 'nullable|date',
            'is_confidential'      => 'nullable|boolean',
            'group_id'             => 'nullable|exists:document_groups,id',
            'file'                 => 'nullable|file|max:10240',
        ]);

        $user = Auth::user();
        $validated['branch_id']  = $user->branch_id;
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();
        $validated['status']     ??= 'draft';
        $validated['visibility'] ??= 'private';

        // Group from URL segment takes precedence over body field
        if ($groupId) {
            $validated['group_id'] = $groupId;
        }

        // ✅ Wrap sequence generation in a transaction to prevent race conditions
        $document = DB::transaction(function () use ($validated, $request) {
            if (! empty($validated['document_prefix_id'])) {
                $prefix = DocumentPrefix::lockForUpdate()->findOrFail($validated['document_prefix_id']);
            } else {
                $prefix = DocumentPrefix::where('is_default', true)->lockForUpdate()->first();
            }

            $validated['document_code'] = $prefix
                ? $prefix->generateNumber()
                : strtoupper(Str::random(10));

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('documents', 'public');
                $validated['file_name'] = $file->getClientOriginalName();
                $validated['file_type'] = $file->getClientOriginalExtension();
                $validated['file_size'] = $file->getSize();
                $validated['mime_type'] = $file->getMimeType();
                $validated['file_path'] = $path;
            }

            // Handle QR / confidential
            if (! empty($validated['is_confidential'])) {
                $token = (string) Str::uuid();
                $validated['verification_token'] = $token;
                $validated['qr_token']           = $token;
                $validated['qr_code_path']       = $this->generateQrCode($token);
            }

            return Document::create($validated);
        });

        return response()->json(
            $document->fresh()->load('group', 'category', 'prefix', 'branch'),
            201
        );
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $document->load(['branch', 'category', 'prefix', 'creator', 'updater', 'group']);
        $document->file_url    = $document->file_path    ? url(Storage::url($document->file_path))    : null;
        $document->qr_code_url = $document->qr_code_path ? url(Storage::url($document->qr_code_path)) : null;

        return response()->json($document);
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'group_id'             => 'nullable|exists:document_groups,id',
            'document_category_id' => 'nullable|exists:document_categories,id',
            'document_prefix_id'   => 'nullable|exists:document_prefixes,id',
            'title'                => 'sometimes|string|max:255',
            'description'          => 'nullable|string',
            'status'               => 'sometimes|in:draft,published,archived,expired',
            'visibility'           => 'sometimes|in:public,private,restricted',
            'expiration_date'      => 'nullable|date|after_or_equal:today',
            'file'                 => 'nullable|file|max:10240',
            'is_confidential'      => 'nullable|boolean',
        ]);

        $validated['updated_by'] = Auth::id();

        // Handle confidential flag changes
        if (isset($validated['is_confidential'])) {
            if ($validated['is_confidential'] && ! $document->verification_token) {
                $token = (string) Str::uuid();
                $validated['verification_token'] = $token;
                $validated['qr_token']           = $token;
                $validated['qr_code_path']       = $this->generateQrCode($token);
            }

            if (! $validated['is_confidential'] && $document->qr_code_path) {
                Storage::disk('public')->delete($document->qr_code_path);
                $validated['verification_token'] = null;
                $validated['qr_token']           = null;
                $validated['qr_code_path']       = null;
            }
        }

        // Handle file replacement
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

        // ✅ Return fresh model with relations — avoids returning stale in-memory data
        $fresh = $document->fresh()->load('group', 'category', 'prefix', 'branch', 'creator', 'updater');
        $fresh->file_url    = $fresh->file_path    ? url(Storage::url($fresh->file_path))    : null;
        $fresh->qr_code_url = $fresh->qr_code_path ? url(Storage::url($fresh->qr_code_path)) : null;

        return response()->json($fresh);
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

        return response()->json(['message' => 'Document deleted successfully.']);
    }

    public function restore(int $id): JsonResponse
    {
        $document = Document::withTrashed()->findOrFail($id);
        $this->authorize('restore', $document);
        $document->restore();

        return response()->json([
            'message'  => 'Document restored successfully.',
            'document' => $document->fresh()->load('group', 'category', 'prefix', 'branch'),
        ]);
    }

    public function verify(string $token): JsonResponse
    {
        // ✅ Eager-load branch to avoid lazy N+1
        $document = Document::with('branch')
            ->where('verification_token', $token)
            ->orWhere('qr_token', $token)
            ->firstOrFail();

        return response()->json([
            'document_code' => $document->document_code,
            'title'         => $document->title,
            'status'        => $document->status,
            'issued_by'     => $document->branch?->name,
            'expiration'    => $document->expiration_date,
        ]);
    }

    public function move(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'group_id' => 'nullable|exists:document_groups,id',
        ]);

        $document->update([
            'group_id'   => $validated['group_id'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'message'  => 'Document moved successfully.',
            'document' => $document->fresh()->load('group'),
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function generateQrCode(string $token): string
    {
        $url     = url("/api/documents/verify/{$token}");
        $qrImage = QrCode::size(300)->generate($url);
        $path    = "qrcodes/{$token}.svg";
        Storage::disk('public')->put($path, $qrImage);
        return $path;
    }
}