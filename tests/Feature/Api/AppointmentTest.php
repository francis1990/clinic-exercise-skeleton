<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    private Patient $patient;

    private Dentist $dentist;

    private Treatment $treatment;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::create([
            'name' => 'Recepcionista',
            'email' => 'recepcionista@test.com',
            'password' => 'secret123',
        ]));

        $this->patient = Patient::create([
            'name' => 'Test Patient',
            'email' => 'patient@test.com',
            'phone' => '+34600000000',
        ]);

        $this->dentist = Dentist::create([
            'name' => 'Roberto',
            'last_name' => 'Garcia Lopez',
            'specialties' => ['Ortodoncia'],
        ]);

        $this->treatment = Treatment::create([
            'name' => 'Brackets',
            'specialty' => 'Ortodoncia',
            'base_price' => 3999.95,
            'duration_minutes' => 45,
        ]);
    }

    public function test_create_appointment_successfully(): void
    {
        $response = $this->postJson('/api/appointments', [
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00',
            'duration' => 45,
            'reason' => 'Colocación de brackets',
            'treatment_ids' => [$this->treatment->id],
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'start_time', 'end_time', 'dentist_id', 'patient_id', 'treatment_ids', 'reason'],
            ]);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
        ]);
    }

    public function test_create_appointment_with_overlap_returns_409(): void
    {
        // Create first appointment
        $this->postJson('/api/appointments', [
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00',
            'duration' => 45,
            'reason' => 'Primera cita',
            'treatment_ids' => [$this->treatment->id],
        ]);

        // Try to create overlapping appointment
        $response = $this->postJson('/api/appointments', [
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:30',
            'duration' => 30,
            'reason' => 'Cita solapada',
            'treatment_ids' => [$this->treatment->id],
        ]);

        $response->assertStatus(409);
    }

    public function test_create_appointment_without_auth_returns_401(): void
    {
        $this->app['auth']->forgetGuards();

        $response = $this->postJson('/api/appointments', [
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00',
            'duration' => 45,
            'reason' => 'Test',
            'treatment_ids' => [$this->treatment->id],
        ]);

        $response->assertUnauthorized();
    }

    public function test_create_appointment_with_invalid_patient_returns_422(): void
    {
        $response = $this->postJson('/api/appointments', [
            'patient_id' => 9999,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00',
            'duration' => 45,
            'reason' => 'Test',
            'treatment_ids' => [$this->treatment->id],
        ]);

        $response->assertUnprocessable();
    }

    public function test_create_appointment_without_treatments_returns_422(): void
    {
        $response = $this->postJson('/api/appointments', [
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00',
            'duration' => 45,
            'reason' => 'Test',
            'treatment_ids' => [],
        ]);

        $response->assertUnprocessable();
    }

    public function test_list_appointments_by_date(): void
    {
        // Create an appointment
        Appointment::create([
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00:00',
            'end_time' => '2026-03-15 09:45:00',
            'reason' => 'Test',
        ]);

        $response = $this->getJson('/api/appointments?date=2026-03-15');

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [['id', 'start_time', 'end_time', 'dentist_id', 'patient_id', 'treatment_ids', 'reason']],
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_list_appointments_without_date_returns_422(): void
    {
        $response = $this->getJson('/api/appointments');

        $response->assertUnprocessable();
    }

    public function test_list_appointments_returns_empty_for_date_without_appointments(): void
    {
        $response = $this->getJson('/api/appointments?date=2026-12-31');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_list_appointments_without_auth_returns_401(): void
    {
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/appointments?date=2026-03-15');

        $response->assertUnauthorized();
    }

    // ── Update tests ──────────────────────────────────────────────

    public function test_update_appointment_successfully(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00:00',
            'end_time' => '2026-03-15 09:45:00',
            'reason' => 'Motivo original',
        ]);
        $appointment->treatments()->attach($this->treatment->id);

        $response = $this->putJson("/api/appointments/{$appointment->id}", [
            'reason' => 'Motivo actualizado',
            'start_time' => '2026-03-15 10:00',
            'duration' => 30,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'start_time', 'end_time', 'dentist_id', 'patient_id', 'treatment_ids', 'reason'],
            ])
            ->assertJsonPath('data.reason', 'Motivo actualizado')
            ->assertJsonPath('data.start_time', '2026-03-15 10:00')
            ->assertJsonPath('data.end_time', '2026-03-15 10:30');
    }

    public function test_update_appointment_with_overlap_returns_409(): void
    {
        // Create two appointments
        Appointment::create([
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00:00',
            'end_time' => '2026-03-15 09:45:00',
            'reason' => 'Primera cita',
        ]);

        $second = Appointment::create([
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 11:00:00',
            'end_time' => '2026-03-15 11:45:00',
            'reason' => 'Segunda cita',
        ]);

        // Try to move second appointment to overlap with first
        $response = $this->putJson("/api/appointments/{$second->id}", [
            'start_time' => '2026-03-15 09:30',
            'duration' => 30,
        ]);

        $response->assertStatus(409);
    }

    public function test_update_appointment_partial_only_reason(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00:00',
            'end_time' => '2026-03-15 09:45:00',
            'reason' => 'Motivo original',
        ]);
        $appointment->treatments()->attach($this->treatment->id);

        $response = $this->putJson("/api/appointments/{$appointment->id}", [
            'reason' => 'Solo cambio el motivo',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.reason', 'Solo cambio el motivo')
            ->assertJsonPath('data.start_time', '2026-03-15 09:00')
            ->assertJsonPath('data.end_time', '2026-03-15 09:45');
    }

    public function test_update_nonexistent_appointment_returns_404(): void
    {
        $response = $this->putJson('/api/appointments/9999', [
            'reason' => 'No existe',
        ]);

        $response->assertNotFound();
    }

    public function test_update_appointment_without_auth_returns_401(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00:00',
            'end_time' => '2026-03-15 09:45:00',
            'reason' => 'Test',
        ]);

        $this->app['auth']->forgetGuards();

        $response = $this->putJson("/api/appointments/{$appointment->id}", [
            'reason' => 'Actualizado',
        ]);

        $response->assertUnauthorized();
    }

    // ── Delete tests ──────────────────────────────────────────────

    public function test_delete_appointment_successfully(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00:00',
            'end_time' => '2026-03-15 09:45:00',
            'reason' => 'Para eliminar',
        ]);
        $appointment->treatments()->attach($this->treatment->id);

        $response = $this->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Cita eliminada correctamente.');

        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    public function test_delete_nonexistent_appointment_returns_404(): void
    {
        $response = $this->deleteJson('/api/appointments/9999');

        $response->assertNotFound();
    }

    public function test_delete_appointment_without_auth_returns_401(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'dentist_id' => $this->dentist->id,
            'start_time' => '2026-03-15 09:00:00',
            'end_time' => '2026-03-15 09:45:00',
            'reason' => 'Test',
        ]);

        $this->app['auth']->forgetGuards();

        $response = $this->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertUnauthorized();
    }
}
