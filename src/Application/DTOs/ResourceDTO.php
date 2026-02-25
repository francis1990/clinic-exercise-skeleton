<?php

declare(strict_types=1);

namespace Booking\Application\DTOs;

use Booking\Domain\Entities\Resource;

final class ResourceDTO
{
    /** @param string[] $specialties */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $lastName,
        public readonly string $fullName,
        public readonly array $specialties
    ) {}

    public static function fromEntity(Resource $resource): self
    {
        return new self(
            id: $resource->id()->value(),
            name: $resource->name(),
            lastName: $resource->lastName(),
            fullName: $resource->fullName(),
            specialties: $resource->specialties()
        );
    }
}
