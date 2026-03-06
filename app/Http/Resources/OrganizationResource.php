<?php
// app/Http/Resources/OrganizationResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'logo' => $this->logo,
            'website' => $this->website,
            'status' => $this->status,
            'settings' => $this->settings,
            'branches_count' => $this->whenCounted('branches'),
            'employees_count' => $this->whenCounted('employees'),
            'users_count' => $this->whenCounted('users'),
            'documents_count' => $this->whenCounted('documents'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}