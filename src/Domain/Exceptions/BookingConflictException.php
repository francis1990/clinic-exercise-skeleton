<?php

declare(strict_types=1);

namespace Booking\Domain\Exceptions;

final class BookingConflictException extends \DomainException
{
    public function __construct(string $message = 'The requested time slot conflicts with an existing booking.')
    {
        parent::__construct($message);
    }
}
