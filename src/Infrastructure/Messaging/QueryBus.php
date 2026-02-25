<?php

declare(strict_types=1);

namespace Booking\Infrastructure\Messaging;

use Booking\Application\Contracts\QueryHandlerInterface;
use Booking\Application\Contracts\QueryInterface;
use Illuminate\Contracts\Container\Container;

final class QueryBus
{
    /** @var array<class-string<QueryInterface>, class-string<QueryHandlerInterface>> */
    private array $handlers = [];

    public function __construct(
        private readonly Container $container
    ) {}

    /**
     * Register a handler for a given query class.
     *
     * @param  class-string<QueryInterface>  $queryClass
     * @param  class-string<QueryHandlerInterface>  $handlerClass
     */
    public function register(string $queryClass, string $handlerClass): void
    {
        $this->handlers[$queryClass] = $handlerClass;
    }

    /**
     * Dispatch a query to its registered handler and return the read-only result.
     */
    public function dispatch(QueryInterface $query): mixed
    {
        $queryClass = get_class($query);

        if (! isset($this->handlers[$queryClass])) {
            throw new \RuntimeException("No handler registered for query: {$queryClass}");
        }

        /** @var QueryHandlerInterface $handler */
        $handler = $this->container->make($this->handlers[$queryClass]);

        return $handler->handle($query);
    }
}
