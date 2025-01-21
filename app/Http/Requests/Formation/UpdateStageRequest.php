<?php

namespace App\Http\Requests\Formation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['superadmin', 'province_admin']);
    }

    public function rules(): array
    {
        return [
            'stage_id' => 'required|exists:formation_stages,id',
            'current_year' => [
                'nullable',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $stage = FormationStage::find($this->stage_id);
                    if ($stage && $stage->has_years && $value > $stage->max_years) {
                        $fail("The current year cannot exceed {$stage->max_years} for this stage.");
                    }
                }
            ],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date'
        ];
    }
} 