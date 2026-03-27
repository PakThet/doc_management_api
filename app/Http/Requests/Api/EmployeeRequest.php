<?php
// app/Http/Requests/Api/EmployeeRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;
        $organizationId = Auth::user()->organization_id;

        return [
            'organization_id' => 'nullable|integer',
            'user_id' => 'nullable|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'employee_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees')->ignore($employeeId)
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees')->ignore($employeeId)
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees')->ignore($employeeId)
            ],
            'address' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,terminated,on_leave',
            'employment_type' => 'sometimes|in:full_time,part_time,contract,intern,temporary',
            'date_of_birth' => 'nullable|date|before:today',
            'join_date' => 'required|date',
            'probation_end_date' => 'nullable|date|after:join_date',
            'confirmation_date' => 'nullable|date|after:join_date',
            'resignation_date' => 'nullable|date',
            'exit_date' => 'nullable|date|after:resignation_date',
            'position' => 'required|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'bank_details' => 'nullable|array',
            'bank_details.account_name' => 'required_with:bank_details|string|max:255',
            'bank_details.account_number' => 'required_with:bank_details|string|max:50',
            'bank_details.bank_name' => 'required_with:bank_details|string|max:255',
            'bank_details.branch' => 'nullable|string|max:255',
            'documents' => 'nullable|array',
            'metadata' => 'nullable|array'
        ];
    }
}
