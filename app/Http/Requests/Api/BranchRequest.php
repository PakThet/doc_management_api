<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('branch')?->id;

        return [
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|unique:branches,code,' . $id,
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}