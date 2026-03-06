<?php
// app/Http/Requests/Api/BranchRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;
        $organizationId = Auth::user()->organization_id;

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('branches')->ignore($branchId)->where(function ($query) use ($organizationId) {
                    return $query->where('organization_id', $organizationId);
                })
            ],
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'established_date' => 'nullable|date',
            'status' => 'sometimes|in:active,inactive',
            'settings' => 'nullable|array'
        ];
    }
}