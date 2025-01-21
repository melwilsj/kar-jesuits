<?php

namespace App\Http\Requests\ProvinceTransfer;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['superadmin', 'province_admin']);
    }

    public function rules(): array
    {
        return [
            'to_province_id' => [
                'required',
                'exists:provinces,id',
                function ($attribute, $value, $fail) {
                    if ($value === $this->route('user')->province_id) {
                        $fail('Cannot transfer to the same province.');
                    }
                }
            ],
            'notes' => 'nullable|string|max:1000'
        ];
    }
} 