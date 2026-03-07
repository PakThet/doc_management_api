<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('organization')?->id;

        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:organizations,slug,' . $id,
            'email' => 'required|email|unique:organizations,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|string',
            'website' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,suspended',
        ];
    }
}