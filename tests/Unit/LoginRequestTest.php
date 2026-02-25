<?php

namespace Tests\Unit;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    private function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, (new LoginRequest)->rules());
    }

    public function test_valid_data_passes(): void
    {
        $this->assertTrue($this->validate([
            'email' => 'user@example.com',
            'password' => 'secret123',
        ])->passes());
    }

    public function test_email_is_required(): void
    {
        $this->assertTrue($this->validate(['password' => 'secret'])->fails());
    }

    public function test_email_must_be_valid_email(): void
    {
        $this->assertTrue($this->validate([
            'email' => 'not-an-email',
            'password' => 'secret',
        ])->fails());
    }

    public function test_password_is_required(): void
    {
        $this->assertTrue($this->validate(['email' => 'user@example.com'])->fails());
    }

    public function test_authorize_returns_true(): void
    {
        $request = new LoginRequest;

        $this->assertTrue($request->authorize());
    }
}
