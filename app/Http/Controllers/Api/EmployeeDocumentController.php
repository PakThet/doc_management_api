<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class EmployeeDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $documents = $employee->documents()->latest()->get();

        return response()->json([
            'message' => 'Documents retrieved successfully',
            'data'    => $documents,
        ]);
    }

    public function upload(Request $request, $employeeId): JsonResponse
    {
        $request->validate([
            'file'        => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'file_type'   => 'required|in:certificate,cv,contract,id_card,other',
            'expiry_date' => 'nullable|date',
        ]);

        $employee = Employee::findOrFail($employeeId);

        $file = $request->file('file');
        $path = $file->store('employees/documents', 'public');

        $document = $employee->documents()->create([
            'file_name'   => $file->getClientOriginalName(),
            'file_type'   => $request->file_type,
            'file_size'   => $file->getSize(),
            'mime_type'   => $file->getMimeType(),
            'file_path'   => $path,
            'expiry_date' => $request->expiry_date,
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data'    => $document,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $document = EmployeeDocument::findOrFail($id);

        return response()->json([
            'message' => 'Document retrieved successfully',
            'data'    => $document,
        ]);
    }

    public function download($id)
    {
        $document = EmployeeDocument::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->download(
            Storage::disk('public')->path($document->file_path),
            $document->file_name
        );
    }

    public function destroy($id): JsonResponse
    {
        $document = EmployeeDocument::findOrFail($id);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }
}