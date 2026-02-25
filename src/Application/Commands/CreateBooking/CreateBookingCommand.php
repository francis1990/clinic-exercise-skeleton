<?php

declare(strict_types=1);

namespace Booking\Application\Commands\CreateBooking;

use Booking\Application\Contracts\CommandInterface;
use Booking\Domain\ValueObjects\DateTimeRange;

final readonly class CreateBookingCommand implements CommandInterface
{
    /**
     * @param  int[]  $treatmentIds
     */
    public function __construct(
        public int $resourceId,
        public int $clientId,
        public DateTimeRange $timeRange,
        public ?string $notes = null,
        public array $treatmentIds = []
    ) {}
}
