<?php

declare(strict_types=1);

namespace Booking\Application\DTOs;

use Booking\Domain\Entities\Client;

final class ClientDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $notes
    ) {}

    public static function fromEntity(Client $client): self
    {
        return new self(
            id: $client->id()->value(),
            name: $client->name(),
            email: $client->email(),
            phone: $client->phone(),
            notes: $client->notes()
        );
    }
}
