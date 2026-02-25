<?php

declare(strict_types=1);

namespace Booking\Application\Commands\CreateBooking;

use Booking\Application\DTOs\BookingDTO;

final class CreateBookingResponse
{
    public function __construct(
        public readonly BookingDTO $booking
    ) {}
}
