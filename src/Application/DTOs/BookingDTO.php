<?php

declare(strict_types=1);

namespace Booking\Application\DTOs;

use Booking\Domain\Entities\Booking;

final class BookingDTO
{
    /** @param int[] $treatmentIds */
    public function __construct(
        public readonly int $id,
        public readonly int $resourceId,
        public readonly int $clientId,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly string $status,
        public readonly ?string $notes,
        public readonly array $treatmentIds
    ) {}

    public static function fromEntity(Booking $booking): self
    {
        return new self(
            id: $booking->id()->value(),
            resourceId: $booking->resourceId()->value(),
            clientId: $booking->clientId()->value(),
            startTime: $booking->timeRange()->start()->format('Y-m-d H:i'),
            endTime: $booking->timeRange()->end()->format('Y-m-d H:i'),
            status: $booking->status()->value(),
            notes: $booking->notes(),
            treatmentIds: $booking->treatmentIds()
        );
    }
}
