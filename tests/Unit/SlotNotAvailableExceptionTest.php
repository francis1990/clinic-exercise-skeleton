<?php

namespace Tests\Unit;

use App\Exceptions\SlotNotAvailableException;
use PHPUnit\Framework\TestCase;

class SlotNotAvailableExceptionTest extends TestCase
{
    public function test_has_default_message(): void
    {
        $exception = new SlotNotAvailableException;

        $this->assertSame(
            'El horario seleccionado no está disponible para este dentista.',
            $exception->getMessage()
        );
    }

    public function test_is_an_exception(): void
    {
        $exception = new SlotNotAvailableException;

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_can_override_message(): void
    {
        $exception = new SlotNotAvailableException('Mensaje personalizado');

        $this->assertSame('Mensaje personalizado', $exception->getMessage());
    }
}
