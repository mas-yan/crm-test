<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    public function test_login_validation_fail()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password'
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_login_with_nonexistent_email()
    {
        // Data login dengan email yang tidak terdaftar
        $this->withoutExceptionHandling();
        $payload = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        // Kirim request POST ke endpoint login
        $response = $this->postJson('/api/auth/login', $payload);

        // Cek status response
        $response->assertStatus(401);

        // Pastikan pesan error sesuai
        $response->assertJson([
            'message' => 'Invalid credentials.',
        ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $this->withoutExceptionHandling();
        $payload = [
            'email' => 'customer@gmail.com',
            'password' => 'wrong-password',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid credentials.',
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example',
            'password' => bcrypt('password')
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => 'test@example',
            'password' => 'password'
        ]);

        $response->assertStatus(200);

        // Ambil token dari respons
        $token = $response->json('token');

        // Kirim permintaan ke endpoint yang membutuhkan autentikasi
        $authenticatedResponse = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/auth/user');

        // Pastikan user berhasil diautentikasi
        $authenticatedResponse->assertStatus(200);
        $authenticatedResponse->assertJson([
            'email' => 'test@example',
        ]);
    }
}
