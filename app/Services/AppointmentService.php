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
     * Check if the requested slot is available for the dentist.
     *
     * @throws \App\Exceptions\SlotNotAvailableException
     */
    private function ensureSlotAvailable(int $dentistId, Carbon $start, Carbon $end): void
    {
        $existingAppointments = Appointment::where('dentist_id', $dentistId)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->get(['start_time', 'end_time'])
            ->map(fn (Appointment $apt) => [
                'start' => $apt->start_time,
                'end' => $apt->end_time,
            ])
            ->toArray();

        if (!$this->schedule->isSlotAvailable($existingAppointments, $start, $end)) {
            throw new \App\Exceptions\SlotNotAvailableException();
        }
    }
}
