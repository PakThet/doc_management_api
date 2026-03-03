<?php
// app/Http/Requests/Api/DocumentCategoryRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class DocumentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('document_category')?->id;
        $organizationId = Auth::user()->organization_id;

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('document_categories')->ignore($categoryId)
            ],
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive',
            'is_system' => 'sometimes|boolean'
        ];
    }
}