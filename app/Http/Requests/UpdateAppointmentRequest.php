<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['sometimes', 'integer', 'exists:patients,id'],
            'dentist_id' => ['sometimes', 'integer', 'exists:dentists,id'],
            'start_time' => ['sometimes', 'date_format:Y-m-d H:i'],
            'duration' => ['sometimes', 'integer', 'min:5', 'max:480'],
            'reason' => ['sometimes', 'string', 'max:500'],
            'treatment_ids' => ['sometimes', 'array', 'min:1'],
            'treatment_ids.*' => ['integer', 'exists:treatments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.date_format' => 'El formato de fecha/hora debe ser Y-m-d H:i (ej: 2024-03-15 09:30).',
            'treatment_ids.min' => 'Debe seleccionar al menos un tratamiento.',
        ];
    }
}
