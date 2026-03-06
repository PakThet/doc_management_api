<?php
// app/Http/Requests/Api/DepartmentRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')?->id;
        $organizationId = Auth::user()->organization_id;

        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:departments,code,' . $departmentId,
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                'exists:departments,id',
                Rule::notIn([$departmentId])
            ],
            'head_of_department_id' => 'nullable|exists:employees,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:active,inactive',
            'metadata' => 'nullable|array'
        ];
    }
}