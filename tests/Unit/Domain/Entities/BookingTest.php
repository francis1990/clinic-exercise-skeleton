<?php

namespace Tests\Unit\Domain\Entities;

use Booking\Domain\Entities\Booking;
use Booking\Domain\Exceptions\BookingConflictException;
use Booking\Domain\ValueObjects\BookingId;
use Booking\Domain\ValueObjects\ClientId;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\ResourceId;
use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase
{
    private function makeBooking(?string $startTime = '2026-03-01 09:00', ?string $endTime = '2026-03-01 10:00'): Booking
    {
        return Booking::create(
            resourceId: ResourceId::fromInt(1),
            clientId: ClientId::fromInt(1),
            timeRange: new DateTimeRange(
                new \DateTimeImmutable($startTime),
                new \DateTimeImmutable($endTime)
            ),
            notes: 'Test booking'
        );
    }

    public function test_new_booking_is_pending(): void
    {
        $booking = $this->makeBooking();

        $this->assertTrue($booking->status()->isPending());
        $this->assertNull($booking->id());
    }

    public function test_can_assign_id(): void
    {
        $booking = $this->makeBooking();
        $booking->assignId(BookingId::fromInt(42));

        $this->assertSame(42, $booking->id()->value());
    }

    public function test_cannot_assign_id_twice(): void
    {
        $booking = $this->makeBooking();
        $booking->assignId(BookingId::fromInt(1));

        $this->expectException(\LogicException::class);
        $booking->assignId(BookingId::fromInt(2));
    }

    public function test_can_confirm_pending_booking(): void
    {
        $booking = $this->makeBooking();
        $booking->confirm();

        $this->assertTrue($booking->status()->isConfirmed());
    }

    public function test_cannot_confirm_cancelled_booking(): void
    {
        $booking = $this->makeBooking();
        $booking->cancel();

        $this->expectException(BookingConflictException::class);
        $booking->confirm();
    }

    public function test_can_cancel_booking(): void
    {
        $booking = $this->makeBooking();
        $booking->cancel();

        $this->assertTrue($booking->status()->isCancelled());
    }

    public function test_cannot_cancel_completed_booking(): void
    {
        $booking = $this->makeBooking();
        $booking->confirm();
        $booking->complete();

        $this->expectException(BookingConflictException::class);
        $booking->cancel();
    }

    public function test_can_reschedule_pending_booking(): void
    {
        $booking = $this->makeBooking();
        $newRange = new DateTimeRange(
            new \DateTimeImmutable('2026-03-02 10:00'),
            new \DateTimeImmutable('2026-03-02 11:00')
        );

        $booking->reschedule($newRange);

        $this->assertTrue($booking->status()->isPending());
        $this->assertTrue($booking->timeRange()->equals($newRange));
    }

    public function test_reschedule_resets_confirmed_to_pending(): void
    {
        $booking = $this->makeBooking();
        $booking->confirm();

        $newRange = new DateTimeRange(
            new \DateTimeImmutable('2026-03-02 10:00'),
            new \DateTimeImmutable('2026-03-02 11:00')
        );

        $booking->reschedule($newRange);

        $this->assertTrue($booking->status()->isPending());
    }

    public function test_cannot_reschedule_cancelled_booking(): void
    {
        $booking = $this->makeBooking();
        $booking->cancel();

        $this->expectException(BookingConflictException::class);
        $booking->reschedule(new DateTimeRange(
            new \DateTimeImmutable('2026-03-02 10:00'),
            new \DateTimeImmutable('2026-03-02 11:00')
        ));
    }

    public function test_can_reconstitute_from_persistence(): void
    {
        $id = BookingId::fromInt(5);
        $resourceId = ResourceId::fromInt(2);
        $clientId = ClientId::fromInt(3);
        $range = new DateTimeRange(
            new \DateTimeImmutable('2026-03-01 09:00'),
            new \DateTimeImmutable('2026-03-01 10:00')
        );

        $booking = Booking::reconstitute(
            id: $id,
            resourceId: $resourceId,
            clientId: $clientId,
            timeRange: $range,
            status: \Booking\Domain\ValueObjects\BookingStatus::confirmed(),
            notes: 'Reconstituted',
            treatmentIds: [1, 2]
        );

        $this->assertSame(5, $booking->id()->value());
        $this->assertTrue($booking->status()->isConfirmed());
        $this->assertSame([1, 2], $booking->treatmentIds());
    }
}
