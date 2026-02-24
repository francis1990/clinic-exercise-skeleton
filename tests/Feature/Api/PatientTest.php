<?php

namespace Tests\Feature\Api;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::create([
            'name' => 'Recepcionista',
            'email' => 'recepcionista@test.com',
            'password' => 'secret123',
        ]));
    }

    public function test_create_patient_successfully(): void
    {
        $response = $this->postJson('/api/patients', [
            'name' => 'Maria Lopez',
            'email' => 'maria@test.com',
            'phone' => '+34612345678',
            'note' => 'Alergia a penicilina',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'email', 'phone', 'note', 'created_at'],
            ]);

        $this->assertDatabaseHas('patients', ['email' => 'maria@test.com']);
    }

    public function test_create_patient_without_auth_returns_401(): void
    {
        Sanctum::actingAs(new User()); // Reset auth
        $this->app['auth']->forgetGuards();

        $response = $this->postJson('/api/patients', [
            'name' => 'Maria Lopez',
            'email' => 'maria@test.com',
            'phone' => '+34612345678',
        ]);

        $response->assertUnauthorized();
    }

    public function test_create_patient_with_duplicate_email_returns_422(): void
    {
        Patient::create([
            'name' => 'Existing',
            'email' => 'maria@test.com',
            'phone' => '123456789',
        ]);

        $response = $this->postJson('/api/patients', [
            'name' => 'Maria Lopez',
            'email' => 'maria@test.com',
            'phone' => '+34612345678',
        ]);

        $response->assertUnprocessable();
    }

    public function test_create_patient_without_required_fields_returns_422(): void
    {
        $response = $this->postJson('/api/patients', []);

        $response->assertUnprocessable();
    }

    public function test_create_patient_with_optional_note_null(): void
    {
        $response = $this->postJson('/api/patients', [
            'name' => 'Carlos Ruiz',
            'email' => 'carlos@test.com',
            'phone' => '+34698765432',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('patients', ['email' => 'carlos@test.com', 'note' => null]);
    }
}
