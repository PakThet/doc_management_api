<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;

        return [
            'branch_id' => 'nullable|exists:branches,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'image' => 'nullable|string',
            'password' => $this->isMethod('post') ? 'required|min:6' : 'nullable|min:6',
            'status' => 'nullable|in:active,inactive,suspended',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ];
    }
}