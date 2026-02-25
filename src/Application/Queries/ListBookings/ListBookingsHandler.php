<?php

declare(strict_types=1);

namespace Booking\Application\Queries\ListBookings;

use Booking\Application\Contracts\QueryHandlerInterface;
use Booking\Application\Contracts\QueryInterface;
use Booking\Application\DTOs\BookingDTO;
use Booking\Domain\Entities\Booking;
use Booking\Domain\Repositories\BookingRepositoryInterface;

final class ListBookingsHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * @return BookingDTO[]
     */
    public function handle(QueryInterface $query): array
    {
        /** @var ListBookingsQuery $query */
        $bookings = $this->bookingRepository->findAll($query->filters);

        return array_map(
            fn (Booking $booking) => BookingDTO::fromEntity($booking),
            $bookings
        );
    }
}
