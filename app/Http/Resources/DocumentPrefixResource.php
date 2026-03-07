<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentPrefixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'department_id' => $this->department_id,
            'name' => $this->name,
            'prefix' => $this->prefix,
            'separator' => $this->separator,
            'format' => $this->format,
            'description' => $this->description,
            'status' => $this->status,
            'is_default' => $this->is_default,
            'metadata' => $this->metadata,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'documents_count' => $this->whenCounted('documents'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
