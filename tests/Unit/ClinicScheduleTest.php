<?php

namespace Tests\Unit;

use App\Domain\Schedule\ClinicSchedule;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ClinicScheduleTest extends TestCase
{
    private ClinicSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schedule = new ClinicSchedule();
    }

    public function test_slot_is_available_when_no_existing_appointments(): void
    {
        $start = new DateTimeImmutable('2026-03-01 09:00');
        $end = new DateTimeImmutable('2026-03-01 10:00');

        $this->assertTrue($this->schedule->isSlotAvailable([], $start, $end));
    }

    public function test_slot_is_not_available_when_full_overlap(): void
    {
        $existing = [
            ['start' => new DateTimeImmutable('2026-03-01 09:00'), 'end' => new DateTimeImmutable('2026-03-01 10:00')],
        ];

        $start = new DateTimeImmutable('2026-03-01 09:00');
        $end = new DateTimeImmutable('2026-03-01 10:00');

        $this->assertFalse($this->schedule->isSlotAvailable($existing, $start, $end));
    }

    public function test_slot_is_not_available_when_partial_overlap_at_start(): void
    {
        $existing = [
            ['start' => new DateTimeImmutable('2026-03-01 08:30'), 'end' => new DateTimeImmutable('2026-03-01 09:30')],
        ];

        $start = new DateTimeImmutable('2026-03-01 09:00');
        $end = new DateTimeImmutable('2026-03-01 10:00');

        $this->assertFalse($this->schedule->isSlotAvailable($existing, $start, $end));
    }

    public function test_slot_is_not_available_when_partial_overlap_at_end(): void
    {
        $existing = [
            ['start' => new DateTimeImmutable('2026-03-01 09:30'), 'end' => new DateTimeImmutable('2026-03-01 10:30')],
        ];

        $start = new DateTimeImmutable('2026-03-01 09:00');
        $end = new DateTimeImmutable('2026-03-01 10:00');

        $this->assertFalse($this->schedule->isSlotAvailable($existing, $start, $end));
    }

    public function test_slot_is_not_available_when_new_slot_contains_existing(): void
    {
        $existing = [
            ['start' => new DateTimeImmutable('2026-03-01 09:15'), 'end' => new DateTimeImmutable('2026-03-01 09:45')],
        ];

        $start = new DateTimeImmutable('2026-03-01 09:00');
        $end = new DateTimeImmutable('2026-03-01 10:00');

        $this->assertFalse($this->schedule->isSlotAvailable($existing, $start, $end));
    }

    public function test_slot_is_available_when_adjacent_at_end(): void
    {
        $existing = [
            ['start' => new DateTimeImmutable('2026-03-01 10:00'), 'end' => new DateTimeImmutable('2026-03-01 11:00')],
        ];

        $start = new DateTimeImmutable('2026-03-01 09:00');
        $end = new DateTimeImmutable('2026-03-01 10:00');

        $this->assertTrue($this->schedule->isSlotAvailable($existing, $start, $end));
    }

    public function test_slot_is_available_when_adjacent_at_start(): void
    {
        $existing = [
            ['start' => new DateTimeImmutable('2026-03-01 08:00'), 'end' => new DateTimeImmutable('2026-03-01 09:00')],
        ];

        $start = new DateTimeImmutable('2026-03-01 09:00');
        $end = new DateTimeImmutable('2026-03-01 10:00');

        $this->assertTrue($this->schedule->isSlotAvailable($existing, $start, $end));
    }

    public function test_slot_is_available_when_no_overlap_with_multiple_appointments(): void
    {
        $existing = [
            ['start' => new DateTimeImmutable('2026-03-01 08:00'), 'end' => new DateTimeImmutable('2026-03-01 09:00')],
            ['start' => new DateTimeImmutable('2026-03-01 10:00'), 'end' => new DateTimeImmutable('2026-03-01 11:00')],
        ];

        $start = new DateTimeImmutable('2026-03-01 09:00');
        $end = new DateTimeImmutable('2026-03-01 10:00');

        $this->assertTrue($this->schedule->isSlotAvailable($existing, $start, $end));
    }

    public function test_slot_is_not_available_when_overlaps_with_one_of_multiple(): void
    {
        $existing = [
            ['start' => new DateTimeImmutable('2026-03-01 08:00'), 'end' => new DateTimeImmutable('2026-03-01 09:00')],
            ['start' => new DateTimeImmutable('2026-03-01 09:30'), 'end' => new DateTimeImmutable('2026-03-01 10:30')],
        ];

        $start = new DateTimeImmutable('2026-03-01 09:00');
        $end = new DateTimeImmutable('2026-03-01 10:00');

        $this->assertFalse($this->schedule->isSlotAvailable($existing, $start, $end));
    }
}
