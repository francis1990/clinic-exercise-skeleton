<?php

declare(strict_types=1);

namespace Booking\Application\Queries\GetBookingDetails;

use Booking\Application\Contracts\QueryInterface;

final readonly class GetBookingDetailsQuery implements QueryInterface
{
    public function __construct(
        public int $bookingId
    ) {}
}
