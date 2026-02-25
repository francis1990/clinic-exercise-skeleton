<?php

namespace Tests\Unit;

use App\Http\Requests\StorePatientRequest;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePatientRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, (new StorePatientRequest)->rules());
    }

    public function test_valid_data_passes(): void
    {
        $this->assertTrue($this->validate([
            'name' => 'Maria Lopez',
            'email' => 'maria@test.com',
            'phone' => '+34612345678',
        ])->passes());
    }

    public function test_name_is_required(): void
    {
        $this->assertTrue($this->validate([
            'email' => 'maria@test.com',
            'phone' => '123',
        ])->fails());
    }

    public function test_name_max_255_characters(): void
    {
        $this->assertTrue($this->validate([
            'name' => str_repeat('a', 256),
            'email' => 'maria@test.com',
            'phone' => '123',
        ])->fails());
    }

    public function test_email_is_required(): void
    {
        $this->assertTrue($this->validate([
            'name' => 'Maria',
            'phone' => '123',
        ])->fails());
    }

    public function test_email_must_be_valid(): void
    {
        $this->assertTrue($this->validate([
            'name' => 'Maria',
            'email' => 'not-an-email',
            'phone' => '123',
        ])->fails());
    }

    public function test_email_must_be_unique(): void
    {
        Patient::create([
            'name' => 'Existing',
            'email' => 'existing@test.com',
            'phone' => '000',
        ]);

        $this->assertTrue($this->validate([
            'name' => 'Maria',
            'email' => 'existing@test.com',
            'phone' => '123',
        ])->fails());
    }

    public function test_phone_is_required(): void
    {
        $this->assertTrue($this->validate([
            'name' => 'Maria',
            'email' => 'maria@test.com',
        ])->fails());
    }

    public function test_phone_max_20_characters(): void
    {
        $this->assertTrue($this->validate([
            'name' => 'Maria',
            'email' => 'maria@test.com',
            'phone' => str_repeat('1', 21),
        ])->fails());
    }

    public function test_note_is_optional(): void
    {
        $this->assertTrue($this->validate([
            'name' => 'Maria',
            'email' => 'maria@test.com',
            'phone' => '123',
        ])->passes());
    }

    public function test_note_can_be_null(): void
    {
        $this->assertTrue($this->validate([
            'name' => 'Maria',
            'email' => 'maria@test.com',
            'phone' => '123',
            'note' => null,
        ])->passes());
    }

    public function test_note_max_1000_characters(): void
    {
        $this->assertTrue($this->validate([
            'name' => 'Maria',
            'email' => 'maria@test.com',
            'phone' => '123',
            'note' => str_repeat('a', 1001),
        ])->fails());
    }

    public function test_authorize_returns_true(): void
    {
        $request = new StorePatientRequest;

        $this->assertTrue($request->authorize());
    }
}
