<?php

declare(strict_types=1);

namespace Booking\Application\Queries\GetBookingDetails;

use Booking\Application\Contracts\QueryHandlerInterface;
use Booking\Application\Contracts\QueryInterface;
use Booking\Application\DTOs\BookingDTO;
use Booking\Application\DTOs\ClientDTO;
use Booking\Application\DTOs\ResourceDTO;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\Repositories\ClientRepositoryInterface;
use Booking\Domain\Repositories\ResourceRepositoryInterface;
use Booking\Domain\ValueObjects\BookingId;

final class GetBookingDetailsHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly ResourceRepositoryInterface $resourceRepository,
        private readonly ClientRepositoryInterface $clientRepository
    ) {}

    public function handle(QueryInterface $query): BookingDetailsDTO
    {
        /** @var GetBookingDetailsQuery $query */
        $booking = $this->bookingRepository->findById(BookingId::fromInt($query->bookingId));

        if ($booking === null) {
            throw new \RuntimeException("Booking #{$query->bookingId} not found.");
        }

        $resource = $this->resourceRepository->findById($booking->resourceId());
        $client = $this->clientRepository->findById($booking->clientId());

        return new BookingDetailsDTO(
            booking: BookingDTO::fromEntity($booking),
            resource: $resource ? ResourceDTO::fromEntity($resource) : null,
            client: $client ? ClientDTO::fromEntity($client) : null
        );
    }
}
