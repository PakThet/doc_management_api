<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DocumentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentCategory = $this->route('documentCategory') ?? $this->route('document_category');
        $id = is_object($documentCategory) ? $documentCategory->id : $documentCategory;

        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:document_categories,slug,' . $id,
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}
