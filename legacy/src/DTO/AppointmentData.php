<?php

declare(strict_types=1);

namespace Legacy\DTO;

use Legacy\Exception\ValidationException;

/**
 * DTO inmutable para los datos de creación de cita.
 *
 * Reemplaza el array asociativo sin tipado del código original
 * por un objeto con validación estricta en construcción.
 */
class AppointmentData
{
    public readonly int $patientId;
    public readonly int $dentistId;
    public readonly string $startTime;
    public readonly string $endTime;
    public readonly string $reason;

    private const DEFAULT_DURATION_MINUTES = 30;

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    public function __construct(array $data)
    {
        $errors = $this->validate($data);

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $this->patientId = (int) $data['paciente_id'];
        $this->dentistId = (int) $data['dentista_id'];

        $startDateTime = $data['fecha'] . ' ' . $data['hora'];
        $this->startTime = $startDateTime;

        $duration = !empty($data['duracion']) ? (int) $data['duracion'] : self::DEFAULT_DURATION_MINUTES;
        $endTimestamp = strtotime($startDateTime) + ($duration * 60);
        $this->endTime = date('Y-m-d H:i:s', $endTimestamp);

        $this->reason = trim((string) ($data['motivo'] ?? ''));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['paciente_id']) || !is_numeric($data['paciente_id']) || (int) $data['paciente_id'] <= 0) {
            $errors['paciente_id'] = 'El ID del paciente es obligatorio y debe ser un entero positivo.';
        }

        if (empty($data['dentista_id']) || !is_numeric($data['dentista_id']) || (int) $data['dentista_id'] <= 0) {
            $errors['dentista_id'] = 'El ID del dentista es obligatorio y debe ser un entero positivo.';
        }

        if (empty($data['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $data['fecha'])) {
            $errors['fecha'] = 'La fecha es obligatoria y debe tener formato Y-m-d.';
        }

        if (empty($data['hora']) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', (string) $data['hora'])) {
            $errors['hora'] = 'La hora es obligatoria y debe tener formato H:i o H:i:s.';
        }

        if (!empty($data['fecha']) && !empty($data['hora'])) {
            $dateTime = $data['fecha'] . ' ' . $data['hora'];
            if (strtotime($dateTime) === false) {
                $errors['fecha_hora'] = 'La combinación de fecha y hora no es válida.';
            }
        }

        if (!empty($data['duracion']) && (!is_numeric($data['duracion']) || (int) $data['duracion'] <= 0)) {
            $errors['duracion'] = 'La duración debe ser un número positivo de minutos.';
        }

        return $errors;
    }
}
