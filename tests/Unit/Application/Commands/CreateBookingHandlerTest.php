<?php

namespace Tests\Unit\Application\Commands;

use Booking\Application\Commands\CreateBooking\CreateBookingCommand;
use Booking\Application\Commands\CreateBooking\CreateBookingHandler;
use Booking\Application\Commands\CreateBooking\CreateBookingResponse;
use Booking\Domain\Entities\Booking;
use Booking\Domain\Exceptions\ResourceNotAvailableException;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\Services\AvailabilityService;
use Booking\Domain\ValueObjects\BookingId;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\ResourceId;
use Booking\Infrastructure\Events\BookingCreated;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateBookingHandlerTest extends TestCase
{
    private BookingRepositoryInterface&MockObject $bookingRepository;

    private AvailabilityService&MockObject $availabilityService;

    private Dispatcher&MockObject $eventDispatcher;

    private CreateBookingHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingRepository = $this->createMock(BookingRepositoryInterface::class);
        $this->availabilityService = $this->createMock(AvailabilityService::class);
        $this->eventDispatcher = $this->createMock(Dispatcher::class);

        $this->handler = new CreateBookingHandler(
            $this->bookingRepository,
            $this->availabilityService,
            $this->eventDispatcher
        );
    }

    public function test_creates_booking_when_slot_is_available(): void
    {
        $timeRange = new DateTimeRange(
            new \DateTimeImmutable('2026-03-01 09:00'),
            new \DateTimeImmutable('2026-03-01 10:00')
        );

        $command = new CreateBookingCommand(
            resourceId: 1,
            clientId: 2,
            timeRange: $timeRange,
            notes: 'Test',
            treatmentIds: [1]
        );

        $this->availabilityService
            ->expects($this->once())
            ->method('isAvailable')
            ->with($this->equalTo(ResourceId::fromInt(1)), $timeRange)
            ->willReturn(true);

        // Simulate what the real repository does: assign a generated ID after persist
        $this->bookingRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Booking $booking) {
                $booking->assignId(BookingId::fromInt(1));
            });

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(BookingCreated::class));

        $response = $this->handler->handle($command);

        $this->assertInstanceOf(CreateBookingResponse::class, $response);
    }

    public function test_throws_when_slot_is_not_available(): void
    {
        $timeRange = new DateTimeRange(
            new \DateTimeImmutable('2026-03-01 09:00'),
            new \DateTimeImmutable('2026-03-01 10:00')
        );

        $command = new CreateBookingCommand(
            resourceId: 1,
            clientId: 2,
            timeRange: $timeRange
        );

        $this->availabilityService
            ->method('isAvailable')
            ->willReturn(false);

        $this->bookingRepository->expects($this->never())->method('save');
        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $this->expectException(ResourceNotAvailableException::class);

        $this->handler->handle($command);
    }
}
