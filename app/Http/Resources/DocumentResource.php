<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,

            'upload_by' => $this->upload_by,

            'category' => new DocumentCategoryResource($this->whenLoaded('category')),
            'organization' => new OrganizationResource($this->whenLoaded('organization')),

            'created_at' => $this->created_at,
        ];


    }
}
