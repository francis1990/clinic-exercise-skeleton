<?php

namespace App\Exceptions;

use Exception;

class SlotNotAvailableException extends Exception
{
    protected $message = 'El horario seleccionado no está disponible para este dentista.';
}
