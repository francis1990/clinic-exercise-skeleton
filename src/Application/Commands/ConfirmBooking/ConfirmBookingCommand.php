<?php

declare(strict_types=1);

namespace Booking\Application\Commands\ConfirmBooking;

use Booking\Application\Contracts\CommandInterface;

final readonly class ConfirmBookingCommand implements CommandInterface
{
    public function __construct(
        public int $bookingId
    ) {}
}
