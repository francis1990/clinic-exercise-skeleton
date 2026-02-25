<?php

declare(strict_types=1);

namespace Booking\Application\Queries\ListBookings;

use Booking\Application\Contracts\QueryInterface;

final readonly class ListBookingsQuery implements QueryInterface
{
    /**
     * @param  array<string, mixed>  $filters  Supported: date (Y-m-d), status, resource_id, client_id
     */
    public function __construct(
        public array $filters = []
    ) {}
}
