<?php
// app/Http/Requests/Api/DocumentRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentId = $this->route('document')?->id;

        return [
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'nullable|exists:employees,id',
            'document_category_id' => 'nullable|exists:document_categories,id',
            'document_prefix_id' => 'nullable|exists:document_prefixes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expiration_date' => 'nullable|date|after:today',
            'status' => 'sometimes|in:draft,published,archived,expired',
            'visibility' => 'sometimes|in:public,private,restricted',
            'file' => 'sometimes|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png', // 10MB max
            'metadata' => 'nullable|array'
        ];
    }
}
