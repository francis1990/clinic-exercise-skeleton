<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateAppointmentRequestTest extends TestCase
{
    use RefreshDatabase;

    private Patient $patient;

    private Dentist $dentist;

    private Treatment $treatment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient = Patient::create(['name' => 'P', 'email' => 'p@t.com', 'phone' => '123']);
        $this->dentist = Dentist::create(['name' => 'D', 'last_name' => 'L', 'specialties' => []]);
        $this->treatment = Treatment::create([
            'name' => 'Brackets',
            'specialty' => 'Ortodoncia',
            'base_price' => 50,
            'duration_minutes' => 30,
        ]);
    }

    private function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, (new UpdateAppointmentRequest)->rules());
    }

    public function test_empty_data_passes_all_fields_optional(): void
    {
        $this->assertTrue($this->validate([])->passes());
    }

    public function test_only_reason_passes(): void
    {
        $this->assertTrue($this->validate(['reason' => 'Nuevo motivo'])->passes());
    }

    public function test_only_start_time_passes(): void
    {
        $this->assertTrue($this->validate(['start_time' => '2026-03-15 10:00'])->passes());
    }

    public function test_only_duration_passes(): void
    {
        $this->assertTrue($this->validate(['duration' => 60])->passes());
    }

    public function test_invalid_start_time_format_fails(): void
    {
        $this->assertTrue($this->validate(['start_time' => '15/03/2026'])->fails());
    }

    public function test_duration_below_minimum_fails(): void
    {
        $this->assertTrue($this->validate(['duration' => 4])->fails());
    }

    public function test_duration_above_maximum_fails(): void
    {
        $this->assertTrue($this->validate(['duration' => 481])->fails());
    }

    public function test_reason_exceeding_max_length_fails(): void
    {
        $this->assertTrue($this->validate(['reason' => str_repeat('a', 501)])->fails());
    }

    public function test_treatment_ids_empty_array_fails(): void
    {
        $this->assertTrue($this->validate(['treatment_ids' => []])->fails());
    }

    public function test_treatment_ids_nonexistent_fails(): void
    {
        $this->assertTrue($this->validate(['treatment_ids' => [9999]])->fails());
    }

    public function test_treatment_ids_valid_passes(): void
    {
        $this->assertTrue($this->validate(['treatment_ids' => [$this->treatment->id]])->passes());
    }

    public function test_patient_id_nonexistent_fails(): void
    {
        $this->assertTrue($this->validate(['patient_id' => 9999])->fails());
    }

    public function test_patient_id_valid_passes(): void
    {
        $this->assertTrue($this->validate(['patient_id' => $this->patient->id])->passes());
    }

    public function test_dentist_id_nonexistent_fails(): void
    {
        $this->assertTrue($this->validate(['dentist_id' => 9999])->fails());
    }

    public function test_dentist_id_valid_passes(): void
    {
        $this->assertTrue($this->validate(['dentist_id' => $this->dentist->id])->passes());
    }

    public function test_authorize_returns_true(): void
    {
        $request = new UpdateAppointmentRequest;

        $this->assertTrue($request->authorize());
    }
}
