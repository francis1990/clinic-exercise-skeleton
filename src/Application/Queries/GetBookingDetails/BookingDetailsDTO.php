<?php

declare(strict_types=1);

namespace Booking\Application\Queries\GetBookingDetails;

use Booking\Application\DTOs\BookingDTO;
use Booking\Application\DTOs\ClientDTO;
use Booking\Application\DTOs\ResourceDTO;

final class BookingDetailsDTO
{
    public function __construct(
        public readonly BookingDTO $booking,
        public readonly ?ResourceDTO $resource,
        public readonly ?ClientDTO $client
    ) {}
}
