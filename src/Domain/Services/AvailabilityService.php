<?php

declare(strict_types=1);

namespace Booking\Domain\Services;

use Booking\Domain\Entities\Resource;
use Booking\Domain\Entities\TimeSlot;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\ValueObjects\BookingId;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\Duration;
use Booking\Domain\ValueObjects\ResourceId;

class AvailabilityService
{
    /** Default working hours: 08:00 – 20:00 */
    private const WORK_START_HOUR = 8;

    private const WORK_END_HOUR = 20;

    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly ConflictDetectionService $conflictDetection
    ) {}

    /**
     * Check if a resource is available for a given time range.
     */
    public function isAvailable(
        ResourceId $resourceId,
        DateTimeRange $range,
        ?BookingId $excludeBookingId = null
    ): bool {
        $existingBookings = $this->bookingRepository->findByResourceAndDateRange($resourceId, $range);

        return ! $this->conflictDetection->hasConflict($existingBookings, $range, $excludeBookingId);
    }

    /**
     * Generate all available time slots for a resource on a given date.
     *
     * @return TimeSlot[]
     */
    public function getAvailableSlots(
        Resource $resource,
        \DateTimeImmutable $date,
        Duration $slotDuration
    ): array {
        $slots = $this->generateSlots($resource->id(), $date, $slotDuration);
        $dayStart = $date->setTime(0, 0);
        $dayEnd = $date->setTime(23, 59, 59);

        $existingBookings = $this->bookingRepository->findByResourceAndDateRange(
            $resource->id(),
            new DateTimeRange($dayStart, $dayEnd)
        );

        foreach ($slots as $slot) {
            foreach ($existingBookings as $booking) {
                if (! $booking->status()->isCancelled() && $booking->timeRange()->overlaps($slot->timeRange())) {
                    $slot->markUnavailable();
                    break;
                }
            }
        }

        return array_values(array_filter($slots, fn (TimeSlot $s) => $s->isAvailable()));
    }

    /**
     * Generate slots covering the working day at the given duration interval.
     *
     * @return TimeSlot[]
     */
    private function generateSlots(
        ResourceId $resourceId,
        \DateTimeImmutable $date,
        Duration $slotDuration
    ): array {
        $slots = [];
        $current = $date->setTime(self::WORK_START_HOUR, 0);
        $workEnd = $date->setTime(self::WORK_END_HOUR, 0);

        while (true) {
            $slotEnd = \DateTimeImmutable::createFromMutable(
                \DateTime::createFromImmutable($current)->modify("+{$slotDuration->minutes()} minutes")
            );

            if ($slotEnd > $workEnd) {
                break;
            }

            $slots[] = new TimeSlot($resourceId, new DateTimeRange($current, $slotEnd));
            $current = $slotEnd;
        }

        return $slots;
    }
}
