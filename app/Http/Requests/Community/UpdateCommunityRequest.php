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
        $community = $this->route('community');
        
        $rules = [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:communities,code,' . $community->id,
            'address' => 'sometimes|string',
            'diocese' => 'nullable|string',
            'taluk' => 'nullable|string',
            'district' => 'nullable|string',
            'state' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'superior_type' => 'sometimes|in:Superior,Rector,Coordinator',
            'is_formation_house' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean'
        ];

        if ($community->isCommonHouse()) {
            $rules['assistancy_id'] = 'sometimes|exists:assistancies,id';
        } else {
            $rules['province_id'] = 'sometimes|exists:provinces,id';
            $rules['region_id'] = 'nullable|exists:regions,id';
            $rules['is_attached_house'] = 'sometimes|boolean';
        }

        return $rules;
    }
} 