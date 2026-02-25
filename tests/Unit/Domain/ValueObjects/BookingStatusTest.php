<?php

namespace Tests\Unit\Domain\ValueObjects;

use Booking\Domain\Exceptions\InvalidBookingStatusException;
use Booking\Domain\ValueObjects\BookingStatus;
use PHPUnit\Framework\TestCase;

class BookingStatusTest extends TestCase
{
    public function test_can_create_pending_status(): void
    {
        $status = BookingStatus::pending();

        $this->assertTrue($status->isPending());
        $this->assertFalse($status->isConfirmed());
        $this->assertFalse($status->isCancelled());
        $this->assertFalse($status->isCompleted());
        $this->assertSame('pending', $status->value());
    }

    public function test_can_create_confirmed_status(): void
    {
        $status = BookingStatus::confirmed();

        $this->assertFalse($status->isPending());
        $this->assertTrue($status->isConfirmed());
        $this->assertSame('confirmed', $status->value());
    }

    public function test_can_create_cancelled_status(): void
    {
        $status = BookingStatus::cancelled();

        $this->assertTrue($status->isCancelled());
        $this->assertSame('cancelled', $status->value());
    }

    public function test_can_create_completed_status(): void
    {
        $status = BookingStatus::completed();

        $this->assertTrue($status->isCompleted());
        $this->assertSame('completed', $status->value());
    }

    public function test_can_create_from_valid_string(): void
    {
        $status = BookingStatus::fromString('confirmed');

        $this->assertTrue($status->isConfirmed());
    }

    public function test_throws_on_invalid_string(): void
    {
        $this->expectException(InvalidBookingStatusException::class);

        BookingStatus::fromString('unknown');
    }

    public function test_equality(): void
    {
        $a = BookingStatus::pending();
        $b = BookingStatus::pending();
        $c = BookingStatus::confirmed();

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
