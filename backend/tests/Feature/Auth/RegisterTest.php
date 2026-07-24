<?php

namespace Tests\Feature\Auth;

use App\Models\Firm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_firm_and_owner_can_register(): void
    {
        $payload = [
            'firm_name' => 'Test & Partners',
            'plan' => 'solo',
            'name' => 'Jordan Blake',
            'email' => 'jordan@testpartners.test',
            'password' => 'SecurePass!123',
            'password_confirmation' => 'SecurePass!123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'email', 'role'], 'access_token']);

        $this->assertDatabaseHas('firms', ['name' => 'Test & Partners']);
        $this->assertDatabaseHas('users', ['email' => 'jordan@testpartners.test', 'role' => 'firm_owner']);

        $firm = Firm::where('name', 'Test & Partners')->first();
        $this->assertNotNull($firm->owner_id);
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'firm_name' => 'Test & Partners',
            'plan' => 'solo',
            'name' => 'Jordan Blake',
            'email' => 'jordan@testpartners.test',
            'password' => 'SecurePass!123',
            'password_confirmation' => 'Mismatch!123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_weak_passwords_are_rejected(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'firm_name' => 'Test & Partners',
            'plan' => 'solo',
            'name' => 'Jordan Blake',
            'email' => 'jordan@testpartners.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }
}
