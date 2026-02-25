<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_time_is_cast_to_datetime(): void
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

        $fresh = Appointment::find($appointment->id);

        $this->assertInstanceOf(\Carbon\Carbon::class, $fresh->start_time);
        $this->assertInstanceOf(\Carbon\Carbon::class, $fresh->end_time);
    }

    public function test_belongs_to_patient_relationship(): void
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

        $this->assertInstanceOf(Patient::class, $appointment->patient);
        $this->assertSame($patient->id, $appointment->patient->id);
    }

    public function test_belongs_to_dentist_relationship(): void
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

        $this->assertInstanceOf(Dentist::class, $appointment->dentist);
        $this->assertSame($dentist->id, $appointment->dentist->id);
    }

    public function test_belongs_to_many_treatments_relationship(): void
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
            'reason' => 'Test',
        ]);
        $appointment->treatments()->attach($treatment->id);

        $this->assertCount(1, $appointment->fresh()->treatments);
        $this->assertSame($treatment->id, $appointment->fresh()->treatments->first()->id);
    }
}
