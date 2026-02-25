<?php

declare(strict_types=1);

namespace Booking\Application\Queries\GetResourceSchedule;

use Booking\Application\Contracts\QueryInterface;

final readonly class GetResourceScheduleQuery implements QueryInterface
{
    public function __construct(
        public int $resourceId,
        public string $date
    ) {}
}
