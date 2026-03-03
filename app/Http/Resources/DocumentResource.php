<?php
// app/Http/Resources/DocumentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'branch_id' => $this->branch_id,
            'document_category_id' => $this->document_category_id,
            'document_prefix_id' => $this->document_prefix_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'document_code' => $this->document_code,
            'verification_token' => $this->verification_token,
            'title' => $this->title,
            'description' => $this->description,
            'expiration_date' => $this->expiration_date,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'file_url' => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'qr_token' => $this->qr_token,
            'qr_code_url' => $this->qr_code_path ? asset('storage/' . $this->qr_code_path) : null,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'category' => new DocumentCategoryResource($this->whenLoaded('category')),
            'prefix' => new DocumentPrefixResource($this->whenLoaded('prefix')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'updater' => new UserResource($this->whenLoaded('updater')),
            'is_expired' => $this->expiration_date ? $this->expiration_date->isPast() : false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}