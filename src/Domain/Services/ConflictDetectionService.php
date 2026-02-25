<?php

declare(strict_types=1);

namespace Booking\Domain\Services;

use Booking\Domain\Entities\Booking;
use Booking\Domain\ValueObjects\BookingId;
use Booking\Domain\ValueObjects\DateTimeRange;

/**
 * Framework-agnostic service for detecting booking time conflicts.
 *
 * Overlap condition: existing.start < proposed.end AND existing.end > proposed.start
 */
final class ConflictDetectionService
{
    /**
     * Check whether any of the given bookings conflict with the proposed range.
     * Optionally exclude a specific booking (useful for reschedule checks).
     *
     * @param  Booking[]  $existingBookings
     */
    public function hasConflict(
        array $existingBookings,
        DateTimeRange $proposedRange,
        ?BookingId $excludeBookingId = null
    ): bool {
        foreach ($existingBookings as $booking) {
            if ($excludeBookingId !== null && $booking->id()?->equals($excludeBookingId)) {
                continue;
            }

            if ($booking->status()->isCancelled()) {
                continue;
            }

            if ($booking->timeRange()->overlaps($proposedRange)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Low-level slot availability check using raw datetime arrays.
     * Preserved for backward compatibility with the legacy ClinicSchedule contract.
     *
     * @param  array<int, array{start: \DateTimeInterface, end: \DateTimeInterface}>  $existingAppointments
     */
    public function isSlotAvailable(
        array $existingAppointments,
        \DateTimeInterface $proposedStart,
        \DateTimeInterface $proposedEnd
    ): bool {
        foreach ($existingAppointments as $appointment) {
            if ($appointment['start'] < $proposedEnd && $appointment['end'] > $proposedStart) {
                return false;
            }
        }

        return true;
    }
}
