<?php

namespace Tests\Unit\Application\Commands;

use Booking\Application\Commands\CancelBooking\CancelBookingCommand;
use Booking\Application\Commands\CancelBooking\CancelBookingHandler;
use Booking\Domain\Entities\Booking;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\ValueObjects\BookingId;
use Booking\Domain\ValueObjects\BookingStatus;
use Booking\Domain\ValueObjects\ClientId;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\ResourceId;
use Booking\Infrastructure\Events\BookingCancelled;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CancelBookingHandlerTest extends TestCase
{
    private BookingRepositoryInterface&MockObject $bookingRepository;

    private Dispatcher&MockObject $eventDispatcher;

    private CancelBookingHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingRepository = $this->createMock(BookingRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(Dispatcher::class);

        $this->handler = new CancelBookingHandler(
            $this->bookingRepository,
            $this->eventDispatcher
        );
    }

    public function test_cancels_existing_booking(): void
    {
        $booking = Booking::reconstitute(
            id: BookingId::fromInt(1),
            resourceId: ResourceId::fromInt(1),
            clientId: ClientId::fromInt(1),
            timeRange: new DateTimeRange(
                new \DateTimeImmutable('2026-03-01 09:00'),
                new \DateTimeImmutable('2026-03-01 10:00')
            ),
            status: BookingStatus::pending()
        );

        $this->bookingRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(BookingId::fromInt(1)))
            ->willReturn($booking);

        $this->bookingRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Booking $b) => $b->status()->isCancelled()));

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(BookingCancelled::class));

        $command = new CancelBookingCommand(bookingId: 1);
        $this->handler->handle($command);
    }

    public function test_throws_when_booking_not_found(): void
    {
        $this->bookingRepository
            ->method('findById')
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $command = new CancelBookingCommand(bookingId: 999);
        $this->handler->handle($command);
    }
}
