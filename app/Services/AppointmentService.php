<?php

namespace App\Services;

use App\Domain\Schedule\ClinicSchedule;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function __construct(
        private readonly ClinicSchedule $schedule,
    ) {}

    /**
     * Create a new appointment after verifying slot availability.
     *
     * @param array{
     *     patient_id: int,
     *     dentist_id: int,
     *     start_time: string,
     *     duration: int,
     *     reason: string,
     *     treatment_ids: array<int>
     * } $data
     *
     * @throws \App\Exceptions\SlotNotAvailableException
     */
    public function create(array $data): Appointment
    {
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $data['start_time']);
        $endTime = $startTime->copy()->addMinutes($data['duration']);

        $this->ensureSlotAvailable($data['dentist_id'], $startTime, $endTime);

        return DB::transaction(function () use ($data, $startTime, $endTime) {
            $appointment = Appointment::create([
                'patient_id' => $data['patient_id'],
                'dentist_id' => $data['dentist_id'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'reason' => $data['reason'],
            ]);

            $appointment->treatments()->attach($data['treatment_ids']);

            return $appointment->load(['patient', 'dentist', 'treatments']);
        });
    }

    /**
     * Update an existing appointment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \App\Exceptions\SlotNotAvailableException
     */
    public function update(Appointment $appointment, array $data): Appointment
    {
        $startTime = isset($data['start_time'])
            ? Carbon::createFromFormat('Y-m-d H:i', $data['start_time'])
            : $appointment->start_time;

        $duration = $data['duration'] ?? null;

        if (isset($data['start_time']) || isset($data['duration'])) {
            $endTime = $startTime->copy()->addMinutes(
                $duration ?? $startTime->diffInMinutes($appointment->end_time)
            );
        } else {
            $endTime = $appointment->end_time;
        }

        $dentistId = $data['dentist_id'] ?? $appointment->dentist_id;

        $this->ensureSlotAvailable($dentistId, $startTime, $endTime, $appointment->id);

        return DB::transaction(function () use ($appointment, $data, $startTime, $endTime) {
            $appointment->update(array_filter([
                'patient_id' => $data['patient_id'] ?? null,
                'dentist_id' => $data['dentist_id'] ?? null,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'reason' => $data['reason'] ?? null,
            ], fn ($value) => $value !== null));

            if (isset($data['treatment_ids'])) {
                $appointment->treatments()->sync($data['treatment_ids']);
            }

            return $appointment->load(['patient', 'dentist', 'treatments']);
        });
    }

    /**
     * Delete an appointment.
     */
    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }

    /**
     * Check if the requested slot is available for the dentist.
     *
     * @throws \App\Exceptions\SlotNotAvailableException
     */
    private function ensureSlotAvailable(int $dentistId, Carbon $start, Carbon $end, ?int $excludeId = null): void
    {
        $query = Appointment::where('dentist_id', $dentistId)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        $existingAppointments = $query
            ->get(['start_time', 'end_time'])
            ->map(fn (Appointment $apt) => [
                'start' => $apt->start_time,
                'end' => $apt->end_time,
            ])
            ->toArray();

        if (! $this->schedule->isSlotAvailable($existingAppointments, $start, $end)) {
            throw new \App\Exceptions\SlotNotAvailableException;
        }
    }
}
