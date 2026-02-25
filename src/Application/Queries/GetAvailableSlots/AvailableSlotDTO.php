<?php

declare(strict_types=1);

namespace Booking\Application\Queries\GetAvailableSlots;

use Booking\Domain\Entities\TimeSlot;

final class AvailableSlotDTO
{
    public function __construct(
        public readonly int $resourceId,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly int $durationMinutes
    ) {}

    public static function fromTimeSlot(TimeSlot $slot): self
    {
        return new self(
            resourceId: $slot->resourceId()->value(),
            startTime: $slot->start()->format('Y-m-d H:i'),
            endTime: $slot->end()->format('Y-m-d H:i'),
            durationMinutes: $slot->duration()->minutes()
        );
    }
}
