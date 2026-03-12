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
    public function upload(Request $request, $employeeId): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120',
            'file_type' => 'required|string',
            'expiry_date' => 'nullable|date'
        ]);

        $employee = Employee::findOrFail($employeeId);

        $file = $request->file('file');
        $path = $file->store('employees/documents', 'public');

        $document = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $request->file_type,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_path' => $path,
            'expiry_date' => $request->expiry_date
        ]);

        return response()->json(['message' => 'Document uploaded', 'data' => $document]);
    }

    public function index($employeeId): JsonResponse
    {
        $documents = EmployeeDocument::where('employee_id', $employeeId)->get();
        return response()->json($documents);
    }

    public function download($id)
    {
        $document = EmployeeDocument::findOrFail($id);

        $filePath = $document->file_path;

        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->download(Storage::disk('public')->path($filePath), $document->file_name);
    }

    public function destroy($id): JsonResponse
    {
        $document = EmployeeDocument::findOrFail($id);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['message' => 'Document deleted']);
    }
}