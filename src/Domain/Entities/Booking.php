<?php

declare(strict_types=1);

namespace Booking\Domain\Entities;

use Booking\Domain\Exceptions\BookingConflictException;
use Booking\Domain\ValueObjects\BookingId;
use Booking\Domain\ValueObjects\BookingStatus;
use Booking\Domain\ValueObjects\ClientId;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\ResourceId;

class Booking
{
    /** @var int[] */
    private array $treatmentIds;

    private function __construct(
        private ?BookingId $id,
        private readonly ResourceId $resourceId,
        private readonly ClientId $clientId,
        private DateTimeRange $timeRange,
        private BookingStatus $status,
        private ?string $notes,
        array $treatmentIds = []
    ) {
        $this->treatmentIds = $treatmentIds;
    }

    /**
     * Factory method: create a new (not yet persisted) booking.
     *
     * @param  int[]  $treatmentIds
     */
    public static function create(
        ResourceId $resourceId,
        ClientId $clientId,
        DateTimeRange $timeRange,
        ?string $notes = null,
        array $treatmentIds = []
    ): self {
        return new self(
            id: null,
            resourceId: $resourceId,
            clientId: $clientId,
            timeRange: $timeRange,
            status: BookingStatus::pending(),
            notes: $notes,
            treatmentIds: $treatmentIds
        );
    }

    /**
     * Factory method: reconstitute an existing booking from persistence.
     *
     * @param  int[]  $treatmentIds
     */
    public static function reconstitute(
        BookingId $id,
        ResourceId $resourceId,
        ClientId $clientId,
        DateTimeRange $timeRange,
        BookingStatus $status,
        ?string $notes = null,
        array $treatmentIds = []
    ): self {
        return new self($id, $resourceId, $clientId, $timeRange, $status, $notes, $treatmentIds);
    }

    /**
     * Assign a persistence-generated ID to a new booking.
     */
    public function assignId(BookingId $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('Booking ID has already been assigned.');
        }

        $this->id = $id;
    }

    public function confirm(): void
    {
        if ($this->status->isCancelled()) {
            throw new BookingConflictException('Cannot confirm a cancelled booking.');
        }

        $this->status = BookingStatus::confirmed();
    }

    public function cancel(): void
    {
        if ($this->status->isCompleted()) {
            throw new BookingConflictException('Cannot cancel a completed booking.');
        }

        $this->status = BookingStatus::cancelled();
    }

    public function complete(): void
    {
        if (! $this->status->isConfirmed()) {
            throw new BookingConflictException('Only confirmed bookings can be marked as completed.');
        }

        $this->status = BookingStatus::completed();
    }

    public function reschedule(DateTimeRange $newTimeRange): void
    {
        if ($this->status->isCancelled()) {
            throw new BookingConflictException('Cannot reschedule a cancelled booking.');
        }

        if ($this->status->isCompleted()) {
            throw new BookingConflictException('Cannot reschedule a completed booking.');
        }

        $this->timeRange = $newTimeRange;
        $this->status = BookingStatus::pending();
    }

    public function id(): ?BookingId
    {
        return $this->id;
    }

    public function resourceId(): ResourceId
    {
        return $this->resourceId;
    }

    public function clientId(): ClientId
    {
        return $this->clientId;
    }

    public function timeRange(): DateTimeRange
    {
        return $this->timeRange;
    }

    public function status(): BookingStatus
    {
        return $this->status;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /** @return int[] */
    public function treatmentIds(): array
    {
        return $this->treatmentIds;
    }
}
