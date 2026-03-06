<?php
// app/Http/Requests/Api/OrganizationRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $organizationId = $this->route('organization')?->id;

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('organizations')->ignore($organizationId)
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('organizations')->ignore($organizationId)
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'status' => 'sometimes|in:active,inactive,suspended',
            'settings' => 'nullable|array'
        ];
    }
}