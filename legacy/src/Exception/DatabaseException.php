<?php

declare(strict_types=1);

namespace Legacy\Exception;

class DatabaseException extends \RuntimeException
{
    public function __construct(string $message = 'Error de base de datos.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
