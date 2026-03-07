<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'image' => $this->image,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'two_factor_enabled' => $this->two_factor_enabled,
            'status' => $this->status,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->getAllPermissions()->pluck('name');
            }),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
