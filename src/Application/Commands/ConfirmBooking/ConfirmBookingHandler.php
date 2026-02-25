<?php

declare(strict_types=1);

namespace Booking\Application\Commands\ConfirmBooking;

use Booking\Application\Contracts\CommandHandlerInterface;
use Booking\Application\Contracts\CommandInterface;
use Booking\Application\DTOs\BookingDTO;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\ValueObjects\BookingId;

final class ConfirmBookingHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository
    ) {}

    public function handle(CommandInterface $command): BookingDTO
    {
        /** @var ConfirmBookingCommand $command */
        $bookingId = BookingId::fromInt($command->bookingId);

        $booking = $this->bookingRepository->findById($bookingId);

        if ($booking === null) {
            throw new \RuntimeException("Booking #{$command->bookingId} not found.");
        }

        $booking->confirm();
        $this->bookingRepository->save($booking);

        return BookingDTO::fromEntity($booking);
    }
}
