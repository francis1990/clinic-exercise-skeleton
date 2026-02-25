<?php

declare(strict_types=1);

namespace Booking\Domain\Exceptions;

final class ResourceNotAvailableException extends \DomainException
{
    public function __construct(string $message = 'The requested resource is not available for the given time slot.')
    {
        parent::__construct($message);
    }
}
