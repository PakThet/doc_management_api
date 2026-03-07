<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('department')?->id;

        return [
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|unique:departments,code,' . $id,
            'parent_id' => 'nullable|exists:departments,id',
            'head_of_department_id' => 'nullable|exists:employees,id',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}