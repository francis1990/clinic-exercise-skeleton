<?php

declare(strict_types=1);

namespace Booking\Domain\ValueObjects;

use Booking\Domain\Exceptions\InvalidTimeSlotException;

final class Duration
{
    private function __construct(
        private readonly int $minutes
    ) {
        if ($minutes <= 0) {
            throw new InvalidTimeSlotException('Duration must be a positive number of minutes.');
        }
    }

    public static function fromMinutes(int $minutes): self
    {
        return new self($minutes);
    }

    public function minutes(): int
    {
        return $this->minutes;
    }

    public function toDateInterval(): \DateInterval
    {
        return new \DateInterval("PT{$this->minutes}M");
    }

    public function equals(self $other): bool
    {
        return $this->minutes === $other->minutes;
    }

    public function __toString(): string
    {
        return "{$this->minutes} minutes";
    }
}
