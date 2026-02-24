<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'name' => 'Recepcionista',
            'email' => 'recepcionista@test.com',
            'password' => 'secret123',
        ]);
    }

    public function test_login_with_valid_credentials_returns_token(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'recepcionista@test.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['token', 'user' => ['id', 'name', 'email']],
            ]);
    }

    public function test_login_with_invalid_password_returns_401(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'recepcionista@test.com',
            'password' => 'wrong',
        ]);

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Credenciales incorrectas.']);
    }

    public function test_login_with_nonexistent_email_returns_401(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'noexiste@test.com',
            'password' => 'secret123',
        ]);

        $response->assertUnauthorized();
    }

    public function test_login_without_required_fields_returns_422(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertUnprocessable();
    }
}
