<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('employee')?->id;

        return [
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'employee_code' => 'required|unique:employees,employee_code,' . $id,
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'nullable|string|unique:employees,phone,' . $id,
            'position' => 'required|string',
            'status' => 'nullable|in:active,inactive,terminated,on_leave',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern,temporary',
        ];
    }
}