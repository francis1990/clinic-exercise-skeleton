<?php

declare(strict_types=1);

namespace Legacy\Exception;

class ValidationException extends \InvalidArgumentException
{
    /** @var array<string, string> */
    private array $errors;

    /**
     * @param array<string, string> $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Error de validación: ' . implode(', ', $errors));
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
