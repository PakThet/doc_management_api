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

        $this->middleware('permission:view documents')->only(['index','show']);
        $this->middleware('permission:create documents')->only(['store']);
        $this->middleware('permission:edit documents')->only(['update']);
        $this->middleware('permission:delete documents')->only(['destroy']);
    }

    public function index(): JsonResponse
    {
        $documents = QueryBuilder::for(Document::visible())
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('visibility'),
                AllowedFilter::exact('branch_id'),
                AllowedFilter::partial('title'),
                AllowedFilter::partial('document_code'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['title','document_code','created_at','expiration_date'])
            ->allowedIncludes(['branch','category','prefix','creator','updater'])
            ->paginate(request()->integer('per_page',15))
            ->appends(request()->query());

        return response()->json($documents);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Document::class);
        $user = Auth::user();
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
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

        if ($request->hasFile('file')) {

            $file = $request->file('file');

            $path = $file->store('documents','public');

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

        return response()->json($document,201);
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view',$document);

        $document->load(['branch','category','prefix','creator','updater']);

        return response()->json($document);
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update',$document);

        $validated = $request->validate([
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

        $validated['updated_by']=Auth::id();

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

            if (!$validated['is_confidential']) {

                if ($document->qr_code_path && Storage::disk('public')->exists($document->qr_code_path)) {
                    Storage::disk('public')->delete($document->qr_code_path);
                }

                $validated['verification_token'] = null;
                $validated['qr_token'] = null;
                $validated['qr_code_path'] = null;
            }
        }

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

        return response()->json($document);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete',$document);
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        if ($document->qr_code_path && Storage::disk('public')->exists($document->qr_code_path)) {
            Storage::disk('public')->delete($document->qr_code_path);
        }

        $document->delete();

        return response()->json([
            'message'=>'Document deleted successfully'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $document = Document::withTrashed()->findOrFail($id);

        $this->authorize('restore',$document);

        $document->restore();

        return response()->json([
            'message'=>'Document restored successfully'
        ]);
    }

    public function verify(string $token): JsonResponse
    {
        $document = Document::where('verification_token',$token)
            ->orWhere('qr_token',$token)
            ->firstOrFail();

        return response()->json([
            'document_code'=>$document->document_code,
            'title'=>$document->title,
            'status'=>$document->status,
            'issued_by'=>$document->branch->name ?? null,
            'expiration'=>$document->expiration_date,
        ]);
    }
}