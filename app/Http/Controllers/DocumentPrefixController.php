<?php

namespace App\Http\Controllers;

use App\Models\DocumentPrefix;
use Illuminate\Http\Request;

class DocumentPrefixController extends Controller
{
    public function index()
    {
        return response()->json(
            DocumentPrefix::latest()->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:10',
            'format_pattern' => 'required|string',
        ]);

        $prefix = DocumentPrefix::create([
            'name' => $request->name,
            'prefix' => $request->prefix,
            'format_pattern' => $request->format_pattern,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Document Prefix created successfully',
            'data' => $prefix
        ], 201);
    }

    public function show(DocumentPrefix $documentPrefix)
    {
        return response()->json($documentPrefix);
    }

    public function update(Request $request, DocumentPrefix $documentPrefix)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'prefix' => 'sometimes|required|string|max:10',
            'format_pattern' => 'sometimes|required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $documentPrefix->update($request->all());

        return response()->json([
            'message' => 'Document Prefix updated successfully',
            'data' => $documentPrefix
        ]);
    }

    public function destroy(DocumentPrefix $documentPrefix)
    {
        $documentPrefix->delete();

        return response()->json([
            'message' => 'Document Prefix deleted successfully'
        ]);
    }
}