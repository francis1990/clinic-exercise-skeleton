<?php

declare(strict_types=1);

namespace Booking\Application\Commands\RescheduleBooking;

use Booking\Application\Contracts\CommandInterface;
use Booking\Domain\ValueObjects\DateTimeRange;

final readonly class RescheduleBookingCommand implements CommandInterface
{
    public function __construct(
        public int $bookingId,
        public DateTimeRange $newTimeRange
    ) {}
}
