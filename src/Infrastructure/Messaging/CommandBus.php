<?php

declare(strict_types=1);

namespace Booking\Infrastructure\Messaging;

use Booking\Application\Contracts\CommandHandlerInterface;
use Booking\Application\Contracts\CommandInterface;
use Illuminate\Contracts\Container\Container;

final class CommandBus
{
    /** @var array<class-string<CommandInterface>, class-string<CommandHandlerInterface>> */
    private array $handlers = [];

    public function __construct(
        private readonly Container $container
    ) {}

    /**
     * Register a handler for a given command class.
     *
     * @param  class-string<CommandInterface>  $commandClass
     * @param  class-string<CommandHandlerInterface>  $handlerClass
     */
    public function register(string $commandClass, string $handlerClass): void
    {
        $this->handlers[$commandClass] = $handlerClass;
    }

    /**
     * Dispatch a command to its registered handler and return the result.
     */
    public function dispatch(CommandInterface $command): mixed
    {
        $commandClass = get_class($command);

        if (! isset($this->handlers[$commandClass])) {
            throw new \RuntimeException("No handler registered for command: {$commandClass}");
        }

        /** @var CommandHandlerInterface $handler */
        $handler = $this->container->make($this->handlers[$commandClass]);

        return $handler->handle($command);
    }
}
