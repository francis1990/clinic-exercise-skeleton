<?php

declare(strict_types=1);

namespace Legacy\Service;

use Legacy\DTO\AppointmentData;
use Legacy\Exception\DatabaseException;
use Legacy\Exception\ValidationException;
use Legacy\Repository\AppointmentRepository;

/**
 * Caso de uso para la creación de citas.
 *
 * Orquesta la validación (vía DTO) y la persistencia (vía Repository),
 * reemplazando la función procedural crearCita() del código original.
 *
 * Ejemplo de uso:
 *
 *   $connection = new DatabaseConnection('localhost', 'clinica', 'user', 'pass');
 *   $repository = new AppointmentRepository($connection);
 *   $creator = new AppointmentCreator($repository);
 *
 *   try {
 *       $id = $creator->execute([
 *           'paciente_id' => 5,
 *           'dentista_id' => 2,
 *           'fecha'       => '2026-03-15',
 *           'hora'        => '09:30',
 *           'duracion'    => 45,
 *           'motivo'      => 'Revisión general',
 *       ]);
 *       echo "Cita creada con ID: {$id}";
 *   } catch (ValidationException $e) {
 *       echo "Errores: " . implode(', ', $e->getErrors());
 *   } catch (DatabaseException $e) {
 *       echo "Error de BD: " . $e->getMessage();
 *   }
 */
class AppointmentCreator
{
    public function __construct(
        private readonly AppointmentRepository $repository,
    ) {}

    /**
     * Crea una nueva cita a partir de los datos proporcionados.
     *
     * @param array<string, mixed> $data Datos de la cita (misma estructura que el legacy).
     * @return int ID de la cita creada.
     * @throws ValidationException Si los datos no son válidos.
     * @throws DatabaseException Si ocurre un error al persistir.
     */
    public function execute(array $data): int
    {
        $appointmentData = new AppointmentData($data);

        return $this->repository->create($appointmentData);
    }
}
