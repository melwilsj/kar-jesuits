<?php

namespace App\Http\Requests\JesuitProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageMemberDetails($this->route('user'));
    }

    public function rules(): array
    {
        return [
            'dob' => 'required|date|before:today',
            'joining_date' => 'required|date|after:dob',
            'priesthood_date' => 'nullable|date|after:joining_date',
            'final_vows_date' => 'nullable|date|after:priesthood_date',
            'academic_qualifications' => 'nullable|array',
            'academic_qualifications.*.degree' => 'required|string',
            'academic_qualifications.*.institution' => 'required|string',
            'academic_qualifications.*.year' => 'required|integer',
            'publications' => 'nullable|array',
            'publications.*.title' => 'required|string',
            'publications.*.year' => 'required|integer',
            'profile_photo' => 'nullable|image|max:2048'
        ];
    }
} 