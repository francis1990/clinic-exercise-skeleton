<?php

declare(strict_types=1);

namespace Booking\Domain\ValueObjects;

use Booking\Domain\Exceptions\InvalidBookingStatusException;

final class BookingStatus
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const CANCELLED = 'cancelled';
    public const COMPLETED = 'completed';

    private const VALID_STATUSES = [
        self::PENDING,
        self::CONFIRMED,
        self::CANCELLED,
        self::COMPLETED,
    ];

    private function __construct(
        private readonly string $value
    ) {}

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function confirmed(): self
    {
        return new self(self::CONFIRMED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function fromString(string $value): self
    {
        if (! in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidBookingStatusException("Invalid booking status: {$value}");
        }

        return new self($value);
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->value === self::CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
