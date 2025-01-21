<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageCommunity($this->route('community'));
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:communities,code,' . $this->route('community')->id,
            'province_id' => 'sometimes|exists:provinces,id',
            'region_id' => 'nullable|exists:regions,id',
            'superior_id' => 'nullable|exists:users,id',
            'address' => 'sometimes|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'superior_type' => 'sometimes|in:rector,superior,coordinator',
            'is_formation_house' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean'
        ];
    }
} 