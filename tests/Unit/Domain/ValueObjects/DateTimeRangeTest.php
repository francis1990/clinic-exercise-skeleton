<?php

namespace Tests\Unit\Domain\ValueObjects;

use Booking\Domain\Exceptions\InvalidTimeSlotException;
use Booking\Domain\ValueObjects\DateTimeRange;
use PHPUnit\Framework\TestCase;

class DateTimeRangeTest extends TestCase
{
    public function test_creates_valid_range(): void
    {
        $start = new \DateTimeImmutable('2026-03-01 09:00');
        $end = new \DateTimeImmutable('2026-03-01 10:00');

        $range = new DateTimeRange($start, $end);

        $this->assertSame($start, $range->start());
        $this->assertSame($end, $range->end());
    }

    public function test_throws_when_end_before_start(): void
    {
        $this->expectException(InvalidTimeSlotException::class);

        new DateTimeRange(
            new \DateTimeImmutable('2026-03-01 10:00'),
            new \DateTimeImmutable('2026-03-01 09:00')
        );
    }

    public function test_throws_when_end_equals_start(): void
    {
        $this->expectException(InvalidTimeSlotException::class);

        $dt = new \DateTimeImmutable('2026-03-01 09:00');
        new DateTimeRange($dt, $dt);
    }

    public function test_detects_overlap(): void
    {
        $range = new DateTimeRange(
            new \DateTimeImmutable('2026-03-01 09:00'),
            new \DateTimeImmutable('2026-03-01 10:00')
        );

        $overlapping = new DateTimeRange(
            new \DateTimeImmutable('2026-03-01 09:30'),
            new \DateTimeImmutable('2026-03-01 10:30')
        );

        $this->assertTrue($range->overlaps($overlapping));
    }

    public function test_adjacent_ranges_do_not_overlap(): void
    {
        $range1 = new DateTimeRange(
            new \DateTimeImmutable('2026-03-01 09:00'),
            new \DateTimeImmutable('2026-03-01 10:00')
        );

        $range2 = new DateTimeRange(
            new \DateTimeImmutable('2026-03-01 10:00'),
            new \DateTimeImmutable('2026-03-01 11:00')
        );

        $this->assertFalse($range1->overlaps($range2));
    }

    public function test_calculates_duration_in_minutes(): void
    {
        $range = new DateTimeRange(
            new \DateTimeImmutable('2026-03-01 09:00'),
            new \DateTimeImmutable('2026-03-01 09:45')
        );

        $this->assertSame(45, $range->durationInMinutes());
    }
}
