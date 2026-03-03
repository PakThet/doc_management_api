<?php
// app/Http/Requests/Api/UserRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'image' => 'nullable|string|max:255',
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users')->ignore($userId)
            ],
            'bio' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,suspended',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'two_factor_enabled' => 'sometimes|boolean',
        ];

        // Password rules for creation
        if ($this->isMethod('POST')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        // Password rules for update (optional)
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['password'] = 'sometimes|string|min:8|confirmed';
        }

        return $rules;
    }
}