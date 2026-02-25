# Sistema Genérico de Reservas y Citas - Especificación del Proyecto

## 🎯 Objetivo del Proyecto
Desarrollar un **módulo genérico de gestión de citas/reservas** que pueda integrarse en cualquier sistema y adaptarse a diferentes contextos (médico, legal, comercial, educativo, etc.), demostrando dominio de:
- Laravel
- Principios SOLID
- Clean Code
- Clean Architecture
- Patrones de diseño
- Testing

---

## 🏗️ Arquitectura Recomendada: **Hexagonal Architecture + Command/Query Separation**

### ¿Por qué Hexagonal Architecture?

**Ventajas para este proyecto:**
1. **Framework agnóstico** - El core business no depende de Laravel
2. **Fácil migración** - Si cambias de framework, solo reemplazas adapters
3. **Testeable** - Lógica de negocio 100% independiente
4. **Escalable** - Puedes agregar nuevos adapters (API REST, GraphQL, CLI)
5. **Demuestra nivel senior** - Arquitectura avanzada muy valorada

### ¿Por qué Command/Query Separation (NO CQRS completo)?

**Decisión arquitectónica:**
- ✅ **Sí:** Separación clara entre Commands (escritura) y Queries (lectura)
- ❌ **No:** CQRS completo con modelos segregados y bases de datos separadas

**Justificación:**
1. **CQRS completo es overkill** para un sistema de reservas a esta escala
2. **Command/Query Separation** da 80% de los beneficios con 20% de la complejidad
3. **Demuestra conocimiento** del patrón sin over-engineering
4. **Fácil evolución** a CQRS completo si el negocio lo requiere después

**Beneficios concretos:**
- Código más organizado y mantenible
- Intención clara: ¿modifica estado o solo consulta?
- Testing más fácil
- Optimizaciones específicas por tipo de operación

### Estructura de carpetas propuesta:

```
src/
├── Domain/                          # Capa de dominio (independiente de todo)
│   ├── Entities/
│   │   ├── Booking.php
│   │   ├── Resource.php            # Médico, sala, abogado, etc.
│   │   ├── Client.php
│   │   ├── Schedule.php
│   │   └── TimeSlot.php
│   ├── ValueObjects/
│   │   ├── BookingId.php
│   │   ├── BookingStatus.php
│   │   ├── Duration.php
│   │   └── DateTimeRange.php
│   ├── Repositories/               # Interfaces (Ports)
│   │   ├── BookingRepositoryInterface.php
│   │   ├── ResourceRepositoryInterface.php
│   │   └── ClientRepositoryInterface.php
│   ├── Services/                   # Lógica de dominio
│   │   ├── BookingService.php
│   │   ├── AvailabilityService.php
│   │   └── ConflictDetectionService.php
│   └── Exceptions/
│       ├── BookingConflictException.php
│       ├── ResourceNotAvailableException.php
│       └── InvalidTimeSlotException.php
│
├── Application/                     # Casos de uso (Command/Query Separation)
│   ├── Commands/                    # Operaciones de ESCRITURA
│   │   ├── CreateBooking/
│   │   │   ├── CreateBookingCommand.php
│   │   │   ├── CreateBookingHandler.php
│   │   │   └── CreateBookingResponse.php
│   │   ├── CancelBooking/
│   │   │   ├── CancelBookingCommand.php
│   │   │   └── CancelBookingHandler.php
│   │   ├── RescheduleBooking/
│   │   │   ├── RescheduleBookingCommand.php
│   │   │   └── RescheduleBookingHandler.php
│   │   └── ConfirmBooking/
│   │       ├── ConfirmBookingCommand.php
│   │       └── ConfirmBookingHandler.php
│   │
│   ├── Queries/                     # Operaciones de LECTURA
│   │   ├── GetAvailableSlots/
│   │   │   ├── GetAvailableSlotsQuery.php
│   │   │   ├── GetAvailableSlotsHandler.php
│   │   │   └── AvailableSlotDTO.php
│   │   ├── GetBookingDetails/
│   │   │   ├── GetBookingDetailsQuery.php
│   │   │   ├── GetBookingDetailsHandler.php
│   │   │   └── BookingDetailsDTO.php
│   │   ├── ListBookings/
│   │   │   ├── ListBookingsQuery.php
│   │   │   └── ListBookingsHandler.php
│   │   └── GetResourceSchedule/
│   │       ├── GetResourceScheduleQuery.php
│   │       └── GetResourceScheduleHandler.php
│   │
│   ├── Contracts/                   # Interfaces compartidas
│   │   ├── CommandInterface.php
│   │   ├── CommandHandlerInterface.php
│   │   ├── QueryInterface.php
│   │   └── QueryHandlerInterface.php
│   │
│   └── DTOs/                        # Data Transfer Objects
│       ├── BookingDTO.php
│       ├── ResourceDTO.php
│       └── AvailableSlotDTO.php
│
├── Infrastructure/                  # Adaptadores (dependen de Laravel)
│   ├── Persistence/
│   │   ├── Eloquent/
│   │   │   ├── Models/
│   │   │   │   ├── BookingModel.php
│   │   │   │   ├── ResourceModel.php
│   │   │   │   └── ClientModel.php
│   │   │   └── Repositories/
│   │   │       ├── EloquentBookingRepository.php
│   │   │       └── EloquentResourceRepository.php
│   │   └── Migrations/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── BookingController.php           # Despacha Commands
│   │   │   └── AvailabilityController.php      # Despacha Queries
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Messaging/                   # Command/Query Bus
│   │   ├── CommandBus.php
│   │   └── QueryBus.php
│   ├── Notifications/
│   │   ├── BookingConfirmedNotification.php
│   │   └── BookingReminderNotification.php
│   └── Events/
│       ├── BookingCreated.php
│       └── BookingCancelled.php
│
└── Presentation/                    # UI (opcional, puede ser API-only)
    └── API/
        └── Routes/
```

---

## 📋 Funcionalidades Core (MVP)

### **Nivel 1: Entidades Básicas**
- ✅ Gestión de Recursos (médicos, abogados, salas, etc.)
- ✅ Gestión de Clientes
- ✅ Definición de horarios de disponibilidad
- ✅ Slots de tiempo configurables (15min, 30min, 1hr, etc.)

### **Nivel 2: Reservas**
- ✅ Crear una reserva/cita
- ✅ Validar disponibilidad
- ✅ Detectar conflictos de horario
- ✅ Estados de reserva: Pendiente, Confirmada, Cancelada, Completada
- ✅ Cancelar reserva
- ✅ Reprogramar reserva

### **Nivel 3: Reglas de Negocio**
- ⬜ Tiempo mínimo de anticipación (ej: no reservar con menos de 2 horas)
- ⬜ Tiempo máximo de anticipación (ej: máximo 3 meses adelante)
- ⬜ Límite de reservas por cliente
- ⬜ Capacidad de recursos (ej: sala con capacidad de 5 personas)
- ⬜ Días/horas bloqueadas (feriados, vacaciones)

### **Nivel 4: Integraciones**
- ✅ Sistema de eventos (BookingCreated, BookingCancelled)
- ⬜ Notificaciones (email/SMS) - confirmación y recordatorios
- ✅ API REST completa
- ⬜ Webhooks para integraciones externas

---

## 🚀 Plan de Entregables (Roadmap)

---

### ✅ FASE 1: Fundamentos - Domain Layer ✨ — COMPLETADA

**Implementado:**
- `src/Domain/Entities/` — Booking, Resource, Client, TimeSlot
- `src/Domain/ValueObjects/` — BookingId, ResourceId, ClientId, BookingStatus, Duration, DateTimeRange
- `src/Domain/Repositories/` — BookingRepositoryInterface, ResourceRepositoryInterface, ClientRepositoryInterface
- `src/Domain/Services/` — AvailabilityService, ConflictDetectionService
- `src/Domain/Exceptions/` — BookingConflictException, ResourceNotAvailableException, InvalidTimeSlotException, InvalidBookingStatusException
- `tests/Unit/Domain/` — BookingTest, BookingStatusTest, DateTimeRangeTest

**SOLID demostrado:**
- SRP: Cada entidad tiene una única responsabilidad
- OCP: Value Objects cerrados a modificación, abiertos a extensión
- LSP: Interfaces bien definidas
- ISP: Repositorios específicos, no genéricos
- DIP: Dependencia de abstracciones, no implementaciones

---

### ✅ FASE 2: Command/Query Separation - Application Layer 🎯 — COMPLETADA

**Implementado:**
- `src/Application/Contracts/` — CommandInterface, CommandHandlerInterface, QueryInterface, QueryHandlerInterface
- `src/Infrastructure/Messaging/` — CommandBus, QueryBus
- `src/Application/Commands/` — CreateBooking, CancelBooking, RescheduleBooking, ConfirmBooking (Command + Handler)
- `src/Application/Queries/` — GetAvailableSlots, GetBookingDetails, ListBookings, GetResourceSchedule (Query + Handler)
- `src/Application/DTOs/` — BookingDTO, ResourceDTO, ClientDTO
- `tests/Unit/Application/` — CreateBookingHandlerTest, CancelBookingHandlerTest, GetAvailableSlotsHandlerTest

**Patrones aplicados:**
- Command Pattern (con handlers dedicados)
- Query Pattern (sin efectos secundarios)
- DTO Pattern (para transferencia de datos)
- Command/Query Bus (desacoplamiento)

---

### ✅ FASE 3: Persistencia - Infrastructure Layer 💾 — COMPLETADA

**Implementado:**
- `src/Infrastructure/Persistence/Eloquent/Repositories/` — EloquentBookingRepository, EloquentResourceRepository, EloquentClientRepository
- `database/migrations/2026_02_25_000001_add_status_to_appointments_table.php`
- Mappers internos en cada repositorio (Model → Entity y viceversa)

**Consideraciones aplicadas:**
- Los modelos Eloquent (`app/Models/`) NO son entidades de dominio
- Repositorios usan Mappers para convertir Model → Entity y viceversa
- Se puede cambiar el ORM sin afectar el dominio

---

### ✅ FASE 4: API y Controllers 🌐 — COMPLETADA

**Implementado:**
- `app/Http/Controllers/Api/BookingController.php` — Despacha Commands y Queries via buses
- `app/Http/Controllers/Api/AvailabilityController.php` — Despacha Queries via bus
- `routes/api.php` — Rutas RESTful completas
- `app/Providers/AppServiceProvider.php` — Registro de bindings DI + buses

**Endpoints disponibles:**
```
GET    /api/bookings                        # Listar reservas (con filtros)
POST   /api/bookings                        # Crear reserva
GET    /api/bookings/{id}                   # Ver detalle
PATCH  /api/bookings/{id}/reschedule        # Reprogramar
POST   /api/bookings/{id}/confirm           # Confirmar
DELETE /api/bookings/{id}                   # Cancelar
GET    /api/resources/{id}/slots            # Slots disponibles
GET    /api/resources/{id}/schedule         # Agenda del recurso
```

**Pendiente en esta fase:**
- ⬜ Documentación OpenAPI/Swagger para nuevos endpoints
- ⬜ Feature tests para endpoints de `/api/bookings` y `/api/resources`

---

### ⬅️ FASE 5: Notificaciones y Eventos 📧 — **SIGUIENTE**

**Objetivo:** Sistema de notificaciones y eventos

**Por implementar:**
1. ⬜ Event Listeners para BookingCreated y BookingCancelled
2. ⬜ Notificación por email: BookingConfirmedNotification
3. ⬜ Notificación por email: BookingReminderNotification (recordatorio)
4. ⬜ Queue system para procesamiento asíncrono
5. ⬜ Tests de eventos y notificaciones

**Commits sugeridos:**
- `feat: add BookingCreated and BookingCancelled event listeners`
- `feat: implement BookingConfirmed email notification`
- `feat: add BookingReminder queued notification`
- `feat: configure queue system for async processing`
- `test: add event listener and notification tests`

---

### ⬜ FASE 6: Reglas de Negocio Avanzadas 🧠

**Objetivo:** Implementar lógica compleja

**Por implementar:**
1. ⬜ Políticas de reserva configurables (tiempo mínimo/máximo de anticipación)
2. ⬜ Sistema de bloqueos (vacaciones, feriados)
3. ⬜ Límites y restricciones por cliente
4. ⬜ Overbooking prevention
5. ⬜ Waiting list (lista de espera)

**Commits sugeridos:**
- `feat: add configurable booking policies`
- `feat: implement resource blocking system`
- `feat: add client booking limits`
- `feat: implement waiting list`

---

### ⬜ FASE 7: Extensibilidad 🔌

**Objetivo:** Demostrar extensibilidad del sistema

**Por implementar:**
1. ⬜ Sistema de plugins/providers
2. ⬜ Webhook system para integraciones
3. ⬜ Multi-tenancy (opcional)
4. ⬜ Custom validation rules configurable
5. ⬜ Adapter para diferentes tipos de recursos (ejemplo concreto)

**Ejemplos de adaptadores a crear:**
```php
// Adapter para clínica médica
MedicalClinicResourceAdapter

// Adapter para bufete de abogados
LegalFirmResourceAdapter

// Adapter para espacios de coworking
CoworkingSpaceAdapter
```

**Commits sugeridos:**
- `feat: add plugin system architecture`
- `feat: implement webhook support`
- `feat: add medical clinic adapter example`
- `docs: add extension guide`

---

## 🧪 Testing Strategy

### Cobertura mínima objetivo: **85%+**

**Por capa:**
1. **Domain Layer:** 95%+ (Unit tests)
   - Entidades
   - Value Objects
   - Servicios de dominio

2. **Application Layer:** 90%+ (Unit tests con mocks)
   - Command/Query Handlers
   - DTOs

3. **Infrastructure Layer:** 80%+ (Integration tests)
   - Repositories
   - Controllers

4. **API:** 85%+ (Feature tests)
   - Endpoints completos
   - Casos de error

**Tipos de tests:**
```
tests/
├── Unit/              # Tests unitarios rápidos        ← Implementado
├── Integration/       # Tests con BD real              ← Pendiente
├── Feature/          # Tests de API end-to-end         ← Parcial (solo legacy)
└── Architecture/     # Arch tests (validar arquitectura) ← Pendiente
```

---

## 📚 Principios SOLID Aplicados

### **Single Responsibility Principle (SRP)**
```php
// ✅ BIEN: Cada clase tiene una responsabilidad
class CreateBookingHandler {
    public function handle(CreateBookingCommand $command) { }
}

class BookingNotificationService {
    public function sendConfirmation(Booking $booking) { }
}

class AvailabilityService {
    public function checkAvailability(Resource $resource, DateTimeRange $range) { }
}
```

### **Open/Closed Principle (OCP)**
```php
// ✅ BIEN: Abierto a extensión, cerrado a modificación
interface NotificationChannelInterface {
    public function send(Booking $booking);
}

class EmailNotification implements NotificationChannelInterface { }
class SmsNotification implements NotificationChannelInterface { }
// Puedes agregar WhatsApp sin modificar código existente
```

### **Liskov Substitution Principle (LSP)**
```php
// ✅ BIEN: Cualquier implementación funciona igual
BookingRepositoryInterface $repo = new EloquentBookingRepository();
// o
BookingRepositoryInterface $repo = new MongoBookingRepository();
```

### **Interface Segregation Principle (ISP)**
```php
// ✅ BIEN: Interfaces específicas por responsabilidad
interface BookingRepositoryInterface { /* solo operaciones de booking */ }
interface ResourceRepositoryInterface { /* solo operaciones de resource */ }
interface ClientRepositoryInterface { /* solo operaciones de client */ }
```

### **Dependency Inversion Principle (DIP)**
```php
// ✅ BIEN: Depende de abstracción (aplicado en todos los handlers)
class CreateBookingHandler {
    public function __construct(
        private BookingRepositoryInterface $repository,  // Interfaz
        private AvailabilityService $availabilityService, // Servicio de dominio
        private Dispatcher $eventDispatcher              // Contrato de Laravel
    ) {}
}
```

---

## 🔧 Herramientas Recomendadas

### Análisis de código
```bash
composer require --dev phpstan/phpstan
composer require --dev squizlabs/php_codesniffer
composer require --dev friendsofphp/php-cs-fixer
```

### Testing
```bash
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel
composer require --dev pestphp/pest-plugin-arch
```

### Documentación
```bash
composer require --dev darkaonline/l5-swagger  # OpenAPI (ya instalado)
```

---

## 📊 Métricas de Éxito

| Métrica | Objetivo | Estado actual |
|---------|----------|---------------|
| Cobertura de tests | 85%+ | ~70% (Unit + Feature parcial) |
| PHPStan level | 8 | Pendiente configurar |
| Zero code smells | SonarQube | Pendiente |
| Arquitectura validada | Arch tests | Pendiente |
| Documentación completa | README + OpenAPI | Parcial (legacy) |
| CI/CD configurado | GitHub Actions | Pendiente |
| Docker setup | docker-compose.yml | ✅ Ya existe |

---

## 📋 Architecture Decision Records (ADRs)

### **ADR-001: Hexagonal Architecture**

**Contexto:** Necesitamos una arquitectura que permita cambiar el framework sin afectar la lógica de negocio.

**Decisión:** Implementar Hexagonal Architecture (Ports & Adapters).

**Razones:**
- Framework agnóstico (fácil migración futura)
- Testabilidad máxima
- Separación clara de responsabilidades
- Demuestra nivel arquitectónico avanzado

**Consecuencias:**
+ Core de negocio 100% independiente de Laravel
+ Testing sin dependencias externas
+ Fácil cambiar de ORM, framework o database
- Curva de aprendizaje más alta
- Más archivos y estructura inicial

---

### **ADR-002: Command/Query Separation (sin CQRS completo)**

**Contexto:** El sistema requiere claridad entre operaciones de lectura y escritura.

**Decisión:** Implementar **Command/Query Separation** pero **NO** CQRS completo con modelos segregados.

**Razones para NO hacer CQRS completo:**
- CQRS completo implica dos modelos de datos, sincronización, eventual consistency
- Escala del proyecto no lo justifica (~1,000 consultas/día y ~200 reservas/día)
- Over-engineering innecesario

**Razones para SÍ hacer Command/Query Separation:**
- Intención explícita: Command = modifica, Query = solo lee
- Testing más fácil: handlers pequeños y enfocados
- Permite optimizaciones selectivas (ej: cachear queries)
- Arquitectura permite evolucionar a CQRS completo si se necesita

---

### **ADR-003: Testing Strategy**

**Decisión:** Testing Pyramid con 85%+ cobertura.

**Distribución objetivo:**
- 70% Unit Tests (Domain + Application)
- 20% Integration Tests (Infrastructure)
- 10% Feature Tests (End-to-End)

---

## 📖 Recursos Adicionales

**Libros:**
- "Clean Architecture" - Robert C. Martin
- "Domain-Driven Design" - Eric Evans
- "Patterns of Enterprise Application Architecture" - Martin Fowler

**Repos de referencia:**
- Laravel DDD: https://github.com/laravel-beyond-crud
- Hexagonal Laravel: https://github.com/CodelyTV/php-ddd-example
