<?php

namespace App\Http\Requests\Province;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProvinceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageProvince($this->route('province'));
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:provinces,code,' . $this->route('province')->id,
            'description' => 'nullable|string',
            'assistancy_id' => 'sometimes|exists:assistancies,id',
            'is_active' => 'sometimes|boolean'
        ];
    }
} 