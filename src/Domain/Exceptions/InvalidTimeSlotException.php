<?php

declare(strict_types=1);

namespace Booking\Domain\Exceptions;

final class InvalidTimeSlotException extends \DomainException
{
    public function __construct(string $message = 'The provided time slot is invalid.')
    {
        parent::__construct($message);
    }
}
