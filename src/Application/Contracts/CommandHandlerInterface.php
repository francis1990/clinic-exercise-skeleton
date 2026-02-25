<?php

declare(strict_types=1);

namespace Booking\Application\Contracts;

/**
 * Contract for all Command Handlers.
 * A handler receives a Command, executes the business logic, and returns a result.
 */
interface CommandHandlerInterface
{
    public function handle(CommandInterface $command): mixed;
}
