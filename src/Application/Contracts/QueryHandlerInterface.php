<?php

declare(strict_types=1);

namespace Booking\Application\Contracts;

/**
 * Contract for all Query Handlers.
 * A handler receives a Query and returns read-only data with no side effects.
 */
interface QueryHandlerInterface
{
    public function handle(QueryInterface $query): mixed;
}
