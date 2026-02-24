# Dental Clinic Scheduling API

API REST para gestionar citas dentales. Permite a la recepcionista autenticarse, crear pacientes, crear citas y listar citas por dia.

## Requisitos

- PHP 8.2+
- Composer 2.x
- MariaDB 11 (o MySQL 8+)
- Docker y Docker Compose (para la base de datos)

## Instalacion y arranque

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd clinic-exercise-skeleton
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

Ajustar las variables de base de datos en `.env` segun tu configuracion local:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=clinic
DB_USERNAME=<tu_usuario_db>
DB_PASSWORD=<tu_password_db>
RECEPTIONIST_PASSWORD=<contraseña_recepcionista>
```

### 4. Levantar la base de datos

```bash
docker compose up -d db
```

Esto arranca MariaDB 11 en el puerto 3307. Esperar unos segundos a que el contenedor este listo.

### 5. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Esto crea las tablas y carga los datos iniciales (recepcionista, dentistas y tratamientos).

### 6. Arrancar el servidor

```bash
php artisan serve
```

La API estara disponible en `http://localhost:8000/api`.

### Alternativa: Todo en Docker

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

La API estara disponible en `http://localhost:8080/api`.

---

## Endpoints de la API

Todos los endpoints (excepto login) requieren autenticacion via Bearer token de Sanctum.

Todas las respuestas siguen el formato:

```json
{
  "message": "Mensaje descriptivo",
  "data": { ... }
}
```

### POST /api/login

Iniciar sesion y obtener token.

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "recepcionista@pruebasmulhacen.com",
    "password": "<RECEPTIONIST_PASSWORD>"
  }'
```

Respuesta (200):

```json
{
  "message": "Inicio de sesion exitoso.",
  "data": {
    "token": "1|abc123...",
    "user": { "id": 1, "name": "Recepcionista", "email": "recepcionista@pruebasmulhacen.com" }
  }
}
```

### POST /api/patients

Crear un paciente.

```bash
curl -X POST http://localhost:8000/api/patients \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "name": "Maria Lopez",
    "email": "maria@test.com",
    "phone": "+34612345678",
    "note": "Alergia a penicilina"
  }'
```

Respuesta (201):

```json
{
  "message": "Paciente creado correctamente.",
  "data": {
    "id": 1,
    "name": "Maria Lopez",
    "email": "maria@test.com",
    "phone": "+34612345678",
    "note": "Alergia a penicilina",
    "created_at": "2026-02-14T10:00:00+00:00"
  }
}
```

### POST /api/appointments

Crear una cita.

```bash
curl -X POST http://localhost:8000/api/appointments \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "patient_id": 1,
    "dentist_id": 1,
    "start_time": "2026-02-15 09:00",
    "duration": 45,
    "reason": "Colocacion de brackets",
    "treatment_ids": [1]
  }'
```

Respuesta (201):

```json
{
  "message": "Cita creada correctamente.",
  "data": {
    "id": 1,
    "start_time": "2026-02-15 09:00",
    "end_time": "2026-02-15 09:45",
    "dentist_id": 1,
    "patient_id": 1,
    "treatment_ids": [1],
    "reason": "Colocacion de brackets"
  }
}
```

Si hay solapamiento con otra cita del mismo dentista, devuelve `409 Conflict`.

### GET /api/appointments?date=YYYY-MM-DD

Listar citas de un dia.

```bash
curl "http://localhost:8000/api/appointments?date=2026-02-15" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Respuesta (200):

```json
{
  "message": "Citas del dia 2026-02-15.",
  "data": [
    {
      "id": 1,
      "start_time": "2026-02-15 09:00",
      "end_time": "2026-02-15 09:45",
      "dentist_id": 1,
      "patient_id": 1,
      "treatment_ids": [1],
      "reason": "Colocacion de brackets"
    }
  ]
}
```

### Errores comunes

| Codigo | Situacion |
|--------|-----------|
| 401 | Credenciales incorrectas o token ausente/invalido |
| 404 | Recurso no encontrado |
| 409 | Solapamiento de horario con otra cita del mismo dentista |
| 422 | Error de validacion (campos requeridos, formatos, etc.) |

---

## Estructura del proyecto

```
app/
  Http/
    Controllers/Api/     # Controladores ligeros
    Requests/            # Form Requests (validacion)
    Resources/           # API Resources (transformacion de respuestas)
  Models/                # Modelos Eloquent
  Services/              # Logica de negocio (AppointmentService)
  Exceptions/            # Excepciones personalizadas
src/
  ClinicSchedule.php     # Componente de scheduling sin dependencias de Laravel
legacy/
  AppointmentPricing.php # Codigo legado (sin modificar)
database/
  migrations/            # Esquema de base de datos
  seeders/               # Datos iniciales
```

## Decisiones de diseno

- **Separacion de responsabilidades**: los controladores son ligeros y delegan la logica de negocio a `AppointmentService`. El componente `ClinicSchedule` en `src/` es framework-agnostic.
- **Validacion**: se usa Form Requests de Laravel para validar las entradas antes de llegar al controlador.
- **Deteccion de solapamientos**: el servicio consulta citas existentes del dentista y usa `ClinicSchedule::isSlotAvailable()` para verificar disponibilidad antes de crear la cita.
- **Respuestas estandarizadas**: todas las respuestas JSON siguen el formato `{message, data}` con codigos HTTP apropiados.
- **Eager loading**: las citas se cargan con sus tratamientos para evitar N+1 queries.
- **Transacciones**: la creacion de citas (appointment + pivot treatments) se ejecuta en una transaccion de base de datos.
- **Especialidades de dentistas como JSON**: almacenadas como array JSON para permitir multiples especialidades por dentista sin necesidad de una tabla intermedia.

## Datos precargados

### Tratamientos

| Nombre | Especialidad | Precio base | Duracion (min) |
|--------|-------------|-------------|----------------|
| Brackets | Ortodoncia | 3999.95 | 45 |
| Composite | Protesis | 680.00 | 60 |
| Exp. Maxilar | Cirugia | 9000.00 | 120 |
| Radiografia panoramica | Diagnosis | 50.00 | 10 |
| Blanqueamiento | General | 199.62 | 20 |

### Dentistas

| ID | Nombre | Especialidades |
|----|--------|---------------|
| 1 | Roberto Garcia Lopez | Ortodoncia |
| 2 | Antonio Sanchez Castro | Ortodoncia, Protesis, Diagnosis |
| 3 | Miguel Diaz Romero | Cirugia, General |
| 4 | Juan Torres Navarro | Todas |

### Recepcionista

| Email | Password |
|-------|----------|
| recepcionista@pruebasmulhacen.com | Definida en `RECEPTIONIST_PASSWORD` del `.env` |
