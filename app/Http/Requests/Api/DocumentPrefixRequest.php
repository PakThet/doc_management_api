<?php
// app/Http/Requests/Api/DocumentPrefixRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class DocumentPrefixRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $prefixId = $this->route('document_prefix')?->id;
        $organizationId = Auth::user()->organization_id;

        return [
            'name' => 'required|string|max:255',
            'prefix' => [
                'required',
                'string',
                'max:20',
                Rule::unique('document_prefixes')->ignore($prefixId)
            ],
            'separator' => 'sometimes|string|max:5',
            'format' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive',
            'is_default' => 'sometimes|boolean',
            'metadata' => 'nullable|array'
        ];
    }
}