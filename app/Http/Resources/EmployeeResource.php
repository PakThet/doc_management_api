<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'department_id' => $this->department_id,
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'employment_type' => $this->employment_type,
            'date_of_birth' => $this->date_of_birth,
            'join_date' => $this->join_date,
            'probation_end_date' => $this->probation_end_date,
            'confirmation_date' => $this->confirmation_date,
            'resignation_date' => $this->resignation_date,
            'exit_date' => $this->exit_date,
            'position' => $this->position,
            'salary' => $this->salary,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'bank_details' => $this->bank_details,
            'documents' => $this->documents,
            'metadata' => $this->metadata,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'headed_departments' => DepartmentResource::collection($this->whenLoaded('headedDepartments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
