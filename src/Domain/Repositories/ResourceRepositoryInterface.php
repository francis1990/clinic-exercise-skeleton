<?php

declare(strict_types=1);

namespace Booking\Domain\Repositories;

use Booking\Domain\Entities\Resource;
use Booking\Domain\ValueObjects\ResourceId;

interface ResourceRepositoryInterface
{
    public function findById(ResourceId $id): ?Resource;

    /**
     * @return Resource[]
     */
    public function findAll(): array;
}
