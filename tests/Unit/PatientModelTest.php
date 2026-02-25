<?php

namespace Tests\Unit;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_fields_are_mass_assignable(): void
    {
        $patient = Patient::create([
            'name' => 'Maria Lopez',
            'email' => 'maria@test.com',
            'phone' => '+34600000000',
            'note' => 'Alergia a penicilina',
        ]);

        $this->assertSame('Maria Lopez', $patient->name);
        $this->assertSame('maria@test.com', $patient->email);
        $this->assertSame('+34600000000', $patient->phone);
        $this->assertSame('Alergia a penicilina', $patient->note);
    }

    public function test_note_can_be_null(): void
    {
        $patient = Patient::create([
            'name' => 'Juan Perez',
            'email' => 'juan@test.com',
            'phone' => '+34600000001',
        ]);

        $this->assertNull($patient->note);
    }

    public function test_has_many_appointments_relationship(): void
    {
        $patient = new Patient;

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $patient->appointments());
    }
}
