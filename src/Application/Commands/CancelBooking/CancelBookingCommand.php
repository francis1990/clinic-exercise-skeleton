<?php

declare(strict_types=1);

namespace Booking\Application\Commands\CancelBooking;

use Booking\Application\Contracts\CommandInterface;

final readonly class CancelBookingCommand implements CommandInterface
{
    public function __construct(
        public int $bookingId
    ) {}
}
