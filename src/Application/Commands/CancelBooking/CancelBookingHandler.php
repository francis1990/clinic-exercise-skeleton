<?php

declare(strict_types=1);

namespace Booking\Application\Commands\CancelBooking;

use Booking\Application\Contracts\CommandHandlerInterface;
use Booking\Application\Contracts\CommandInterface;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\ValueObjects\BookingId;
use Booking\Infrastructure\Events\BookingCancelled;
use Illuminate\Contracts\Events\Dispatcher;

final class CancelBookingHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly Dispatcher $eventDispatcher
    ) {}

    public function handle(CommandInterface $command): null
    {
        /** @var CancelBookingCommand $command */
        $bookingId = BookingId::fromInt($command->bookingId);

        $booking = $this->bookingRepository->findById($bookingId);

        if ($booking === null) {
            throw new \RuntimeException("Booking #{$command->bookingId} not found.");
        }

        $booking->cancel();
        $this->bookingRepository->save($booking);

        $this->eventDispatcher->dispatch(new BookingCancelled($booking));

        return null;
    }
}
