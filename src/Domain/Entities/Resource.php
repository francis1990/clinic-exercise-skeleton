<?php

declare(strict_types=1);

namespace Booking\Domain\Entities;

use Booking\Domain\ValueObjects\ResourceId;

class Resource
{
    /** @var string[] */
    private array $specialties;

    public function __construct(
        private readonly ResourceId $id,
        private readonly string $name,
        private readonly string $lastName,
        array $specialties = []
    ) {
        $this->specialties = $specialties;
    }

    public function id(): ResourceId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function fullName(): string
    {
        return "{$this->name} {$this->lastName}";
    }

    /** @return string[] */
    public function specialties(): array
    {
        return $this->specialties;
    }

    public function hasSpecialty(string $specialty): bool
    {
        return in_array($specialty, $this->specialties, true);
    }
}
