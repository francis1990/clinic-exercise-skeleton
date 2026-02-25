<?php

declare(strict_types=1);

namespace Booking\Infrastructure\Events;

use Booking\Domain\Entities\Booking;

final class BookingCancelled
{
    public function __construct(
        public readonly Booking $booking
    ) {}
}
