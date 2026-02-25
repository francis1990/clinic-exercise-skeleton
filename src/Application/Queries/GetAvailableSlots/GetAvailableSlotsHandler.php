<?php

declare(strict_types=1);

namespace Booking\Application\Queries\GetAvailableSlots;

use Booking\Application\Contracts\QueryHandlerInterface;
use Booking\Application\Contracts\QueryInterface;
use Booking\Domain\Entities\TimeSlot;
use Booking\Domain\Repositories\ResourceRepositoryInterface;
use Booking\Domain\Services\AvailabilityService;
use Booking\Domain\ValueObjects\ResourceId;

final class GetAvailableSlotsHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly AvailabilityService $availabilityService,
        private readonly ResourceRepositoryInterface $resourceRepository
    ) {}

    /**
     * @return AvailableSlotDTO[]
     */
    public function handle(QueryInterface $query): array
    {
        /** @var GetAvailableSlotsQuery $query */
        $resource = $this->resourceRepository->findById(ResourceId::fromInt($query->resourceId));

        if ($resource === null) {
            throw new \RuntimeException("Resource #{$query->resourceId} not found.");
        }

        $availableSlots = $this->availabilityService->getAvailableSlots(
            $resource,
            $query->date,
            $query->slotDuration
        );

        return array_map(
            fn (TimeSlot $slot) => AvailableSlotDTO::fromTimeSlot($slot),
            $availableSlots
        );
    }
}
