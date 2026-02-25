<?php

declare(strict_types=1);

namespace Booking\Domain\Repositories;

use Booking\Domain\Entities\Client;
use Booking\Domain\ValueObjects\ClientId;

interface ClientRepositoryInterface
{
    public function findById(ClientId $id): ?Client;

    public function save(Client $client): void;

    /**
     * @return Client[]
     */
    public function findAll(): array;
}
