<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DocumentPrefixRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentPrefix = $this->route('documentPrefix') ?? $this->route('document_prefix');
        $id = is_object($documentPrefix) ? $documentPrefix->id : $documentPrefix;

        return [
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:20|unique:document_prefixes,prefix,' . $id,
            'separator' => 'nullable|string|max:5',
            'format' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'is_default' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ];
    }
}
