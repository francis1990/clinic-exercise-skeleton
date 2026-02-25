<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Dental Clinic Scheduling API',
    description: 'API REST para gestionar citas de una clinica dental. Permite autenticacion, creacion de pacientes, creacion de citas y listado de citas por dia.',
    contact: new OA\Contact(email: 'recepcionista@pruebasmulhacen.com')
)]
#[OA\Server(url: 'http://localhost:8000', description: 'Local')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Token obtenido via POST /api/login'
)]
abstract class Controller
{
    //
}
