<?php
// app/Http/Resources/DepartmentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'head_of_department_id' => $this->head_of_department_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location,
            'budget' => $this->budget,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'parent' => new DepartmentResource($this->whenLoaded('parent')),
            'children' => DepartmentResource::collection($this->whenLoaded('children')),
            'head_of_department' => new EmployeeResource($this->whenLoaded('headOfDepartment')),
            'employees_count' => $this->whenCounted('employees'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}