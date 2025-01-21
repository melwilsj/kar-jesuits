<?php

namespace App\Http\Requests\Formation;

use App\Models\FormationStage;
use Illuminate\Foundation\Http\FormRequest;

class CreateFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['superadmin', 'province_admin']);
    }

    public function rules(): array
    {
        return [
            'stage_id' => [
                'required',
                'exists:formation_stages,id',
                function ($attribute, $value, $fail) {
                    $stage = FormationStage::find($value);
                    if ($stage && $stage->has_years && !$this->current_year) {
                        $fail("Current year is required for {$stage->name}.");
                    }
                }
            ],
            'current_year' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date'
        ];
    }
} 