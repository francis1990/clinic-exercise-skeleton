<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SlotNotAvailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService,
    ) {}

    #[OA\Get(
        path: '/api/appointments',
        summary: 'Listar citas por dia',
        description: 'Devuelve todas las citas de un dia concreto, ordenadas por hora de inicio. Requiere autenticacion.',
        security: [['bearerAuth' => []]],
        tags: ['Citas'],
        parameters: [
            new OA\Parameter(
                name: 'date',
                in: 'query',
                required: true,
                description: 'Fecha en formato Y-m-d',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-03-15')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de citas del dia',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Citas del día 2026-03-15.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'start_time', type: 'string', example: '2026-03-15 09:00'),
                                    new OA\Property(property: 'end_time', type: 'string', example: '2026-03-15 09:45'),
                                    new OA\Property(property: 'dentist_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'patient_id', type: 'integer', example: 1),
                                    new OA\Property(
                                        property: 'treatment_ids',
                                        type: 'array',
                                        items: new OA\Items(type: 'integer'),
                                        example: [1]
                                    ),
                                    new OA\Property(property: 'reason', type: 'string', example: 'Colocacion de brackets'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(
                response: 422,
                description: 'Parametro date requerido o formato invalido',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Error de validación.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $request->query('date'));

        $appointments = Appointment::with('treatments')
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'message' => 'Citas del día '.$date->format('Y-m-d').'.',
            'data' => AppointmentResource::collection($appointments),
        ]);
    }

    #[OA\Post(
        path: '/api/appointments',
        summary: 'Crear cita',
        description: 'Crea una nueva cita dental. Valida solapamiento de horarios por dentista. Requiere autenticacion.',
        security: [['bearerAuth' => []]],
        tags: ['Citas'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['patient_id', 'dentist_id', 'start_time', 'duration', 'reason', 'treatment_ids'],
                properties: [
                    new OA\Property(property: 'patient_id', type: 'integer', example: 1),
                    new OA\Property(property: 'dentist_id', type: 'integer', example: 1),
                    new OA\Property(property: 'start_time', type: 'string', format: 'date-time', description: 'Formato Y-m-d H:i', example: '2026-03-15 09:00'),
                    new OA\Property(property: 'duration', type: 'integer', minimum: 5, maximum: 480, description: 'Duracion en minutos', example: 45),
                    new OA\Property(property: 'reason', type: 'string', maxLength: 500, example: 'Colocacion de brackets'),
                    new OA\Property(
                        property: 'treatment_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        minItems: 1,
                        example: [1]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Cita creada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Cita creada correctamente.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'start_time', type: 'string', example: '2026-03-15 09:00'),
                                new OA\Property(property: 'end_time', type: 'string', example: '2026-03-15 09:45'),
                                new OA\Property(property: 'dentist_id', type: 'integer', example: 1),
                                new OA\Property(property: 'patient_id', type: 'integer', example: 1),
                                new OA\Property(
                                    property: 'treatment_ids',
                                    type: 'array',
                                    items: new OA\Items(type: 'integer'),
                                    example: [1]
                                ),
                                new OA\Property(property: 'reason', type: 'string', example: 'Colocacion de brackets'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(
                response: 409,
                description: 'Solapamiento de horario con otra cita del mismo dentista',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'El horario seleccionado no está disponible para este dentista.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validacion',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Error de validación.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->appointmentService->create($request->validated());

            return response()->json([
                'message' => 'Cita creada correctamente.',
                'data' => new AppointmentResource($appointment),
            ], 201);
        } catch (SlotNotAvailableException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 409);
        }
    }

    #[OA\Put(
        path: '/api/appointments/{appointment}',
        summary: 'Actualizar cita',
        description: 'Actualiza una cita existente. Permite actualización parcial. Valida solapamiento de horarios. Requiere autenticacion.',
        security: [['bearerAuth' => []]],
        tags: ['Citas'],
        parameters: [
            new OA\Parameter(
                name: 'appointment',
                in: 'path',
                required: true,
                description: 'ID de la cita',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'patient_id', type: 'integer', example: 1),
                    new OA\Property(property: 'dentist_id', type: 'integer', example: 1),
                    new OA\Property(property: 'start_time', type: 'string', format: 'date-time', description: 'Formato Y-m-d H:i', example: '2026-03-15 09:00'),
                    new OA\Property(property: 'duration', type: 'integer', minimum: 5, maximum: 480, description: 'Duracion en minutos', example: 45),
                    new OA\Property(property: 'reason', type: 'string', maxLength: 500, example: 'Revision general'),
                    new OA\Property(
                        property: 'treatment_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        minItems: 1,
                        example: [1]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cita actualizada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Cita actualizada correctamente.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'start_time', type: 'string', example: '2026-03-15 09:00'),
                                new OA\Property(property: 'end_time', type: 'string', example: '2026-03-15 09:45'),
                                new OA\Property(property: 'dentist_id', type: 'integer', example: 1),
                                new OA\Property(property: 'patient_id', type: 'integer', example: 1),
                                new OA\Property(
                                    property: 'treatment_ids',
                                    type: 'array',
                                    items: new OA\Items(type: 'integer'),
                                    example: [1]
                                ),
                                new OA\Property(property: 'reason', type: 'string', example: 'Revision general'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Cita no encontrada'),
            new OA\Response(
                response: 409,
                description: 'Solapamiento de horario con otra cita del mismo dentista',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'El horario seleccionado no está disponible para este dentista.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validacion',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Error de validación.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        try {
            $appointment = $this->appointmentService->update($appointment, $request->validated());

            return response()->json([
                'message' => 'Cita actualizada correctamente.',
                'data' => new AppointmentResource($appointment),
            ]);
        } catch (SlotNotAvailableException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 409);
        }
    }

    #[OA\Delete(
        path: '/api/appointments/{appointment}',
        summary: 'Eliminar cita',
        description: 'Elimina una cita existente. Requiere autenticacion.',
        security: [['bearerAuth' => []]],
        tags: ['Citas'],
        parameters: [
            new OA\Parameter(
                name: 'appointment',
                in: 'path',
                required: true,
                description: 'ID de la cita',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cita eliminada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Cita eliminada correctamente.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Cita no encontrada'),
        ]
    )]
    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->appointmentService->delete($appointment);

        return response()->json([
            'message' => 'Cita eliminada correctamente.',
            'data' => null,
        ]);
    }
}
