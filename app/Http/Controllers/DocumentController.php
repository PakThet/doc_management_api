<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = QueryBuilder::for(Document::class)
            ->with(['branch', 'documentCategory', 'creator'])

            ->allowedFilters([
                'branch_id',
                'document_category_id',
                'verification_status',

                AllowedFilter::partial('title'),
                AllowedFilter::partial('document_code'),

                AllowedFilter::scope('expired'),
            ])

            ->allowedSorts([
                'title',
                'document_code',
                'created_at',
                'expiration_date'
            ])

            ->defaultSort('-created_at')

            ->paginate(10);

        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'document_category_id' => 'required|exists:document_categories,id',
            'document_prefix_id' => 'required|exists:document_prefixes,id',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:4096',
            'expiration_date' => 'nullable|date'
        ]);

        DB::beginTransaction();

        try {
            $prefix = \App\Models\DocumentPrefix::findOrFail(
                $request->document_prefix_id
            );

            $documentCode = $this->generateDocumentCode($prefix);

            $file = $request->file('file');
            $filePath = $file->store('documents', 'public');

            $token = Str::uuid();
            $verificationUrl = url('/verify-document/' . $token);

            $qr = QrCode::format('svg')->size(300)->generate($verificationUrl);
            $qrPath = 'qrcodes/' . $token . '.svg';
            Storage::disk('public')->put($qrPath, $qr);

            $document = Document::create([
                'branch_id' => $request->branch_id,
                'document_category_id' => $request->document_category_id,
                'created_by' => Auth::id(),
                'verification_token' => $token,
                'title' => $request->title,
                'document_code' => $documentCode,
                'description' => $request->description,
                'expiration_date' => $request->expiration_date,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'qr_code_path' => $qrPath,
            ]);
            DB::commit();
            return response()->json([
                'message' => 'Document created successfully',
                'data' => $document
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Document $document)
    {
        return response()->json(
            $document->load(['branch', 'documentCategory', 'creator'])
        );
    }

    public function update(Request $request, Document $document)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'file' => 'nullable|file|max:4096',
            'verification_status' => 'sometimes|in:active,inactive',
        ]);
        DB::beginTransaction();

        try {

            if ($request->hasFile('file')) {

                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $file = $request->file('file');
                $filePath = $file->store('documents', 'public');

                $document->file_path = $filePath;
                $document->file_type = $file->getClientOriginalExtension();
                $document->file_size = $file->getSize();
            }

            $document->update($request->except('file'));

            DB::commit();

            return response()->json([
                'message' => 'Document updated successfully',
                'data' => $document
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Document $document)
    {
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        if ($document->qr_code_path) {
            Storage::disk('public')->delete($document->qr_code_path);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully'
        ]);
    }

    public function verify($token)
    {
        $document = Document::where('verification_token', $token)->first();

        if (!$document) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid QR Code'
            ], 404);
        }

        $isExpired = $document->expiration_date &&
            Carbon::now()->gt($document->expiration_date);

        return response()->json([
            'status' => 'success',
            'message' => 'This is an official document',
            'document' => $document
        ]);
    }


    private function generateDocumentCode($prefixRecord)
    {
        $format = $prefixRecord->format_pattern;

        $format = str_replace('YYYY', now()->format('Y'), $format);
        $format = str_replace('MM', now()->format('m'), $format);
        $format = str_replace('DD', now()->format('d'), $format);

        $count = Document::whereDate('created_at', now()->toDateString())->count() + 1;
        $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);

        $format = str_replace('XXX', $sequence, $format);

        return $format;
    }
}
