<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_time' => $this->start_time->format('Y-m-d H:i'),
            'end_time' => $this->end_time->format('Y-m-d H:i'),
            'dentist_id' => $this->dentist_id,
            'patient_id' => $this->patient_id,
            'treatment_ids' => $this->treatments->pluck('id')->values(),
            'reason' => $this->reason,
        ];
    }
}
