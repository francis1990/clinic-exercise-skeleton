<?php

declare(strict_types=1);

namespace Booking\Domain\Entities;

use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\Duration;
use Booking\Domain\ValueObjects\ResourceId;

final class TimeSlot
{
    public function __construct(
        private readonly ResourceId $resourceId,
        private readonly DateTimeRange $timeRange,
        private bool $available = true
    ) {}

    public function resourceId(): ResourceId
    {
        return $this->resourceId;
    }

    public function timeRange(): DateTimeRange
    {
        return $this->timeRange;
    }

    public function start(): \DateTimeImmutable
    {
        return $this->timeRange->start();
    }

    public function end(): \DateTimeImmutable
    {
        return $this->timeRange->end();
    }

    public function duration(): Duration
    {
        return Duration::fromMinutes($this->timeRange->durationInMinutes());
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function markUnavailable(): void
    {
        $this->available = false;
    }
}
