<?php

declare(strict_types=1);

namespace Booking\Application\Queries\GetAvailableSlots;

use Booking\Application\Contracts\QueryInterface;
use Booking\Domain\ValueObjects\Duration;

final readonly class GetAvailableSlotsQuery implements QueryInterface
{
    public function __construct(
        public int $resourceId,
        public \DateTimeImmutable $date,
        public Duration $slotDuration
    ) {}
}
