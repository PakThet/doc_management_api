<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('document')?->id;

        return [
            'branch_id' => 'required|exists:branches,id',
            'document_category_id' => 'nullable|exists:document_categories,id',
            'document_prefix_id' => 'nullable|exists:document_prefixes,id',
            'document_code' => 'required|unique:documents,document_code,' . $id,
            'verification_token' => 'nullable|unique:documents,verification_token,' . $id,
            'title' => 'required|string',
            'description' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'status' => 'nullable|in:draft,published,archived,expired',
            'visibility' => 'nullable|in:public,private,restricted',
        ];
    }
}