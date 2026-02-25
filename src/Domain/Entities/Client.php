<?php

declare(strict_types=1);

namespace Booking\Domain\Entities;

use Booking\Domain\ValueObjects\ClientId;

class Client
{
    public function __construct(
        private readonly ClientId $id,
        private readonly string $name,
        private readonly string $email,
        private readonly ?string $phone = null,
        private readonly ?string $notes = null
    ) {}

    public function id(): ClientId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }
}
