<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->route('role');
        $id = is_object($role) ? $role->id : $role;

        return [
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'guard_name' => 'nullable|string|max:255',
        ];
    }
}
