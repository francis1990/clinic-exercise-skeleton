<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PatientController extends Controller
{
    #[OA\Post(
        path: '/api/patients',
        summary: 'Crear paciente',
        description: 'Crea un nuevo paciente en el sistema. Requiere autenticacion.',
        security: [['bearerAuth' => []]],
        tags: ['Pacientes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'phone'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Maria Lopez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@test.com'),
                    new OA\Property(property: 'phone', type: 'string', maxLength: 20, example: '+34612345678'),
                    new OA\Property(property: 'note', type: 'string', maxLength: 1000, nullable: true, example: 'Alergia a penicilina'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Paciente creado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Paciente creado correctamente.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Maria Lopez'),
                                new OA\Property(property: 'email', type: 'string', example: 'maria@test.com'),
                                new OA\Property(property: 'phone', type: 'string', example: '+34612345678'),
                                new OA\Property(property: 'note', type: 'string', nullable: true, example: 'Alergia a penicilina'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-02-14T10:00:00+00:00'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(
                response: 422,
                description: 'Error de validacion (campos requeridos, email duplicado, etc.)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Error de validación.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = Patient::create($request->validated());

        return response()->json([
            'message' => 'Paciente creado correctamente.',
            'data' => new PatientResource($patient),
        ], 201);
    }
}
