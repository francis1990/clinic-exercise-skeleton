<?php

declare(strict_types=1);

namespace Booking\Application\Commands\CreateBooking;

use Booking\Application\Contracts\CommandHandlerInterface;
use Booking\Application\Contracts\CommandInterface;
use Booking\Application\DTOs\BookingDTO;
use Booking\Domain\Entities\Booking;
use Booking\Domain\Exceptions\ResourceNotAvailableException;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\Services\AvailabilityService;
use Booking\Domain\ValueObjects\ClientId;
use Booking\Domain\ValueObjects\ResourceId;
use Illuminate\Contracts\Events\Dispatcher;
use Booking\Infrastructure\Events\BookingCreated;

final class CreateBookingHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly AvailabilityService $availabilityService,
        private readonly Dispatcher $eventDispatcher
    ) {}

    public function handle(CommandInterface $command): CreateBookingResponse
    {
        /** @var CreateBookingCommand $command */
        $resourceId = ResourceId::fromInt($command->resourceId);

        if (! $this->availabilityService->isAvailable($resourceId, $command->timeRange)) {
            throw new ResourceNotAvailableException;
        }

        $booking = Booking::create(
            resourceId: $resourceId,
            clientId: ClientId::fromInt($command->clientId),
            timeRange: $command->timeRange,
            notes: $command->notes,
            treatmentIds: $command->treatmentIds
        );

        $this->bookingRepository->save($booking);

        $this->eventDispatcher->dispatch(new BookingCreated($booking));

        return new CreateBookingResponse(BookingDTO::fromEntity($booking));
    }
}
