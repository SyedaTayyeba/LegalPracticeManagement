<?php

namespace Tests\Feature\Auth;

use App\Models\Firm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(): void
    {
        $firm = Firm::factory()->create();
        $user = User::factory()->for($firm)->create([
            'email' => 'lawyer@test.test',
            'password' => Hash::make('CorrectPass!123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'lawyer@test.test',
            'password' => 'CorrectPass!123',
        ]);

        $response->assertOk()->assertJsonStructure(['access_token', 'user' => ['id', 'email']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['email' => 'lawyer@test.test']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'lawyer@test.test',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(422);
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'suspended@test.test',
            'password' => Hash::make('CorrectPass!123'),
            'status' => 'suspended',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'suspended@test.test',
            'password' => 'CorrectPass!123',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $response->assertOk()->assertJsonPath('email', $user->email);
    }
}
