<?php

namespace Tests\Unit;

use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PatientResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_array_returns_expected_structure(): void
    {
        $patient = Patient::create([
            'name' => 'Maria Lopez',
            'email' => 'maria@test.com',
            'phone' => '+34612345678',
            'note' => 'Alergia a penicilina',
        ]);

        $resource = new PatientResource($patient);
        $result = $resource->toArray(new Request);

        $this->assertSame($patient->id, $result['id']);
        $this->assertSame('Maria Lopez', $result['name']);
        $this->assertSame('maria@test.com', $result['email']);
        $this->assertSame('+34612345678', $result['phone']);
        $this->assertSame('Alergia a penicilina', $result['note']);
        $this->assertArrayHasKey('created_at', $result);
    }

    public function test_created_at_is_iso8601_format(): void
    {
        $patient = Patient::create([
            'name' => 'Juan',
            'email' => 'juan@test.com',
            'phone' => '123',
        ]);

        $resource = new PatientResource($patient);
        $result = $resource->toArray(new Request);

        // ISO 8601 format contains a T separator
        $this->assertStringContainsString('T', $result['created_at']);
    }

    public function test_note_null_when_not_provided(): void
    {
        $patient = Patient::create([
            'name' => 'Ana',
            'email' => 'ana@test.com',
            'phone' => '456',
        ]);

        $resource = new PatientResource($patient);
        $result = $resource->toArray(new Request);

        $this->assertNull($result['note']);
    }
}
