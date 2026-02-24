<?php

declare(strict_types=1);

namespace Legacy\Repository;

use Legacy\Database\DatabaseConnection;
use Legacy\DTO\AppointmentData;
use Legacy\Exception\DatabaseException;
use PDOException;

/**
 * Repositorio para persistencia de citas.
 *
 * Reemplaza la query con concatenación directa (SQL injection)
 * del código original por prepared statements con PDO.
 */
class AppointmentRepository
{
    public function __construct(
        private readonly DatabaseConnection $connection,
    ) {}

    /**
     * Inserta una nueva cita en la base de datos.
     *
     * @return int ID de la cita creada.
     * @throws DatabaseException
     */
    public function create(AppointmentData $data): int
    {
        $sql = <<<'SQL'
            INSERT INTO citas (paciente_id, dentista_id, inicio, fin, motivo)
            VALUES (:paciente_id, :dentista_id, :inicio, :fin, :motivo)
        SQL;

        try {
            $pdo = $this->connection->getPdo();
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':paciente_id' => $data->patientId,
                ':dentista_id' => $data->dentistId,
                ':inicio'      => $data->startTime,
                ':fin'         => $data->endTime,
                ':motivo'      => $data->reason,
            ]);

            $id = (int) $pdo->lastInsertId();

            if ($id === 0) {
                throw new DatabaseException('No se pudo crear la cita.');
            }

            return $id;
        } catch (PDOException $e) {
            throw new DatabaseException(
                'Error al insertar la cita en la base de datos.',
                (int) $e->getCode(),
                $e
            );
        }
    }
}
