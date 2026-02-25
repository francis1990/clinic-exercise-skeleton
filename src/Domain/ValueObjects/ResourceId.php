<?php

declare(strict_types=1);

namespace Booking\Domain\ValueObjects;

final class ResourceId
{
    private function __construct(
        private readonly int $value
    ) {}

    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
