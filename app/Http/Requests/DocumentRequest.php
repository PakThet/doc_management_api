<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
            'organization_id' => 'required|exists:organizations,id',
            'document_category_id' => 'required|exists:document_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',

            'file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120',

            'upload_by' => 'required|string|max:255',
        ];
    }
}
