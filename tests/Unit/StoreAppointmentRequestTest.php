<?php

namespace Tests\Unit;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreAppointmentRequestTest extends TestCase
{
    use RefreshDatabase;

    private array $validData;

    protected function setUp(): void
    {
        parent::setUp();

        $patient = Patient::create(['name' => 'P', 'email' => 'p@t.com', 'phone' => '123']);
        $dentist = Dentist::create(['name' => 'D', 'last_name' => 'L', 'specialties' => []]);
        $treatment = Treatment::create([
            'name' => 'Brackets',
            'specialty' => 'Ortodoncia',
            'base_price' => 50,
            'duration_minutes' => 30,
        ]);

        $this->validData = [
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'start_time' => '2026-03-15 09:00',
            'duration' => 45,
            'reason' => 'Colocación de brackets',
            'treatment_ids' => [$treatment->id],
        ];
    }

    private function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, (new StoreAppointmentRequest)->rules());
    }

    public function test_valid_data_passes(): void
    {
        $this->assertTrue($this->validate($this->validData)->passes());
    }

    public function test_patient_id_is_required(): void
    {
        $data = $this->validData;
        unset($data['patient_id']);

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_dentist_id_is_required(): void
    {
        $data = $this->validData;
        unset($data['dentist_id']);

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_start_time_is_required(): void
    {
        $data = $this->validData;
        unset($data['start_time']);

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_start_time_must_be_valid_format(): void
    {
        $data = $this->validData;
        $data['start_time'] = '15/03/2026 09:00';

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_duration_is_required(): void
    {
        $data = $this->validData;
        unset($data['duration']);

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_duration_minimum_is_5(): void
    {
        $data = $this->validData;
        $data['duration'] = 4;

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_duration_maximum_is_480(): void
    {
        $data = $this->validData;
        $data['duration'] = 481;

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_duration_5_passes(): void
    {
        $data = $this->validData;
        $data['duration'] = 5;

        $this->assertTrue($this->validate($data)->passes());
    }

    public function test_duration_480_passes(): void
    {
        $data = $this->validData;
        $data['duration'] = 480;

        $this->assertTrue($this->validate($data)->passes());
    }

    public function test_reason_is_required(): void
    {
        $data = $this->validData;
        unset($data['reason']);

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_reason_max_500_characters(): void
    {
        $data = $this->validData;
        $data['reason'] = str_repeat('a', 501);

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_treatment_ids_is_required(): void
    {
        $data = $this->validData;
        unset($data['treatment_ids']);

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_treatment_ids_must_have_at_least_one(): void
    {
        $data = $this->validData;
        $data['treatment_ids'] = [];

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_treatment_ids_must_exist_in_database(): void
    {
        $data = $this->validData;
        $data['treatment_ids'] = [9999];

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_patient_id_must_exist_in_database(): void
    {
        $data = $this->validData;
        $data['patient_id'] = 9999;

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_dentist_id_must_exist_in_database(): void
    {
        $data = $this->validData;
        $data['dentist_id'] = 9999;

        $this->assertTrue($this->validate($data)->fails());
    }

    public function test_authorize_returns_true(): void
    {
        $request = new StoreAppointmentRequest;

        $this->assertTrue($request->authorize());
    }
}
