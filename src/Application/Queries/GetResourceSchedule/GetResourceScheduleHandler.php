<?php

declare(strict_types=1);

namespace Booking\Application\Queries\GetResourceSchedule;

use Booking\Application\Contracts\QueryHandlerInterface;
use Booking\Application\Contracts\QueryInterface;
use Booking\Application\DTOs\BookingDTO;
use Booking\Domain\Entities\Booking;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\Repositories\ResourceRepositoryInterface;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\ResourceId;

final class GetResourceScheduleHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly ResourceRepositoryInterface $resourceRepository
    ) {}

    /**
     * @return BookingDTO[]
     */
    public function handle(QueryInterface $query): array
    {
        /** @var GetResourceScheduleQuery $query */
        $resourceId = ResourceId::fromInt($query->resourceId);

        $resource = $this->resourceRepository->findById($resourceId);

        if ($resource === null) {
            throw new \RuntimeException("Resource #{$query->resourceId} not found.");
        }

        $date = new \DateTimeImmutable($query->date);
        $dayRange = new DateTimeRange(
            $date->setTime(0, 0),
            $date->setTime(23, 59, 59)
        );

        $bookings = $this->bookingRepository->findByResourceAndDateRange($resourceId, $dayRange);

        return array_map(
            fn (Booking $booking) => BookingDTO::fromEntity($booking),
            $bookings
        );
    }
}
