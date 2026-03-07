<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permission = $this->route('permission');
        $id = is_object($permission) ? $permission->id : $permission;

        return [
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'guard_name' => 'nullable|string|max:255',
        ];
    }
}
