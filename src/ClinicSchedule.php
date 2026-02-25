<?php

declare(strict_types=1);

namespace App\Domain\Schedule;

use DateTimeInterface;

/**
 * Framework-agnostic component for clinic scheduling logic.
 *
 * Determines whether a proposed time slot is available given
 * a set of existing appointments for the same dentist.
 */
class ClinicSchedule
{
    /**
     * Check if a time slot is available (no overlap with existing appointments).
     *
     * Overlap exists when: existing.start < new.end AND existing.end > new.start
     *
     * @param  array<int, array{start: DateTimeInterface, end: DateTimeInterface}>  $existingAppointments
     * @return bool True if the slot is available, false if there is a conflict.
     */
    public function isSlotAvailable(
        array $existingAppointments,
        DateTimeInterface $proposedStart,
        DateTimeInterface $proposedEnd,
    ): bool {
        foreach ($existingAppointments as $appointment) {
            if ($appointment['start'] < $proposedEnd && $appointment['end'] > $proposedStart) {
                return false;
            }
        }

        return true;
    }
}
