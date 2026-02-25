<?php

declare(strict_types=1);

namespace Booking\Application\Commands\RescheduleBooking;

use Booking\Application\Contracts\CommandHandlerInterface;
use Booking\Application\Contracts\CommandInterface;
use Booking\Application\DTOs\BookingDTO;
use Booking\Domain\Exceptions\ResourceNotAvailableException;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\Services\AvailabilityService;
use Booking\Domain\ValueObjects\BookingId;

final class RescheduleBookingHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly AvailabilityService $availabilityService
    ) {}

    public function handle(CommandInterface $command): BookingDTO
    {
        /** @var RescheduleBookingCommand $command */
        $bookingId = BookingId::fromInt($command->bookingId);

        $booking = $this->bookingRepository->findById($bookingId);

        if ($booking === null) {
            throw new \RuntimeException("Booking #{$command->bookingId} not found.");
        }

        if (! $this->availabilityService->isAvailable($booking->resourceId(), $command->newTimeRange, $bookingId)) {
            throw new ResourceNotAvailableException;
        }

        $booking->reschedule($command->newTimeRange);
        $this->bookingRepository->save($booking);

        return BookingDTO::fromEntity($booking);
    }
}
