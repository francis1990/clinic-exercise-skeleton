<?php

namespace Tests\Unit;

use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AppointmentResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_array_returns_expected_structure(): void
    {
        $patient = Patient::create(['name' => 'P', 'email' => 'p@t.com', 'phone' => '123']);
        $dentist = Dentist::create(['name' => 'D', 'last_name' => 'L', 'specialties' => []]);
        $treatment = Treatment::create([
            'name' => 'Brackets',
            'specialty' => 'Ortodoncia',
            'base_price' => 3999.95,
            'duration_minutes' => 45,
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'start_time' => '2026-03-15 09:00:00',
            'end_time' => '2026-03-15 09:45:00',
            'reason' => 'Colocación de brackets',
        ]);
        $appointment->treatments()->attach($treatment->id);
        $appointment->load('treatments');

        $resource = new AppointmentResource($appointment);
        $result = $resource->toArray(new Request);

        $this->assertSame($appointment->id, $result['id']);
        $this->assertSame('2026-03-15 09:00', $result['start_time']);
        $this->assertSame('2026-03-15 09:45', $result['end_time']);
        $this->assertSame($dentist->id, $result['dentist_id']);
        $this->assertSame($patient->id, $result['patient_id']);
        $this->assertSame('Colocación de brackets', $result['reason']);
        $this->assertEquals([$treatment->id], $result['treatment_ids']->toArray());
    }

    public function test_formats_datetime_without_seconds(): void
    {
        $patient = Patient::create(['name' => 'P', 'email' => 'p@t.com', 'phone' => '123']);
        $dentist = Dentist::create(['name' => 'D', 'last_name' => 'L', 'specialties' => []]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'start_time' => '2026-03-15 14:30:00',
            'end_time' => '2026-03-15 15:15:00',
            'reason' => 'Test',
        ]);
        $appointment->load('treatments');

        $resource = new AppointmentResource($appointment);
        $result = $resource->toArray(new Request);

        $this->assertSame('2026-03-15 14:30', $result['start_time']);
        $this->assertSame('2026-03-15 15:15', $result['end_time']);
    }

    public function test_treatment_ids_empty_when_no_treatments(): void
    {
        $patient = Patient::create(['name' => 'P', 'email' => 'p@t.com', 'phone' => '123']);
        $dentist = Dentist::create(['name' => 'D', 'last_name' => 'L', 'specialties' => []]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'start_time' => '2026-03-15 09:00:00',
            'end_time' => '2026-03-15 09:45:00',
            'reason' => 'Test',
        ]);
        $appointment->load('treatments');

        $resource = new AppointmentResource($appointment);
        $result = $resource->toArray(new Request);

        $this->assertTrue($result['treatment_ids']->isEmpty());
    }
}
