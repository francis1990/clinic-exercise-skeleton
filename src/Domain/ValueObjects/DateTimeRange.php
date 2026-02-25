<?php

declare(strict_types=1);

namespace Booking\Domain\ValueObjects;

use Booking\Domain\Exceptions\InvalidTimeSlotException;

final class DateTimeRange
{
    public function __construct(
        private readonly \DateTimeImmutable $start,
        private readonly \DateTimeImmutable $end
    ) {
        if ($end <= $start) {
            throw new InvalidTimeSlotException('End time must be after start time.');
        }
    }

    public static function fromStrings(string $start, string $end): self
    {
        return new self(
            new \DateTimeImmutable($start),
            new \DateTimeImmutable($end)
        );
    }

    public static function fromStartAndDuration(\DateTimeImmutable $start, Duration $duration): self
    {
        $end = \DateTimeImmutable::createFromMutable(
            \DateTime::createFromImmutable($start)->modify("+{$duration->minutes()} minutes")
        );

        return new self($start, $end);
    }

    public function start(): \DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): \DateTimeImmutable
    {
        return $this->end;
    }

    public function overlaps(self $other): bool
    {
        return $this->start < $other->end && $this->end > $other->start;
    }

    public function durationInMinutes(): int
    {
        return (int) (($this->end->getTimestamp() - $this->start->getTimestamp()) / 60);
    }

    public function contains(\DateTimeImmutable $dateTime): bool
    {
        return $dateTime >= $this->start && $dateTime < $this->end;
    }

    public function equals(self $other): bool
    {
        return $this->start == $other->start && $this->end == $other->end;
    }
}
