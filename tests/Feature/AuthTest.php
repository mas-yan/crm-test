<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Fungsi untuk melakukan request login dengan payload yang diberikan
    private function postLogin(array $payload)
    {
        return $this->postJson('/api/auth/login', $payload);
    }

    // Menguji validasi login gagal ketika email tidak valid
    public function test_login_validation_fail()
    {
        $response = $this->postLogin([
            'email' => 'invalid-email', // Email tidak valid
            'password' => 'password'
        ]);

        $response->assertStatus(422); // Memastikan status respons adalah 422 (Unprocessable Entity)
    }

    // Menguji pengguna tidak dapat login dengan email yang tidak ada
    public function test_user_cannot_login_with_nonexistent_email()
    {
        $payload = [
            'email' => 'nonexistent@example.com', // Email yang tidak terdaftar
            'password' => 'password123',
        ];

        $response = $this->postLogin($payload);

        $response->assertStatus(401); // Memastikan status respons adalah 401 (Unauthorized)
        $response->assertJson(['message' => 'Invalid credentials.']); // Memastikan pesan error yang diterima
    }

    // Menguji pengguna tidak dapat login dengan kredensial yang salah
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $payload = [
            'email' => 'customer@gmail.com', // Email yang terdaftar
            'password' => 'wrong-password', // Password yang salah
        ];

        $response = $this->postLogin($payload);

        $response->assertStatus(401); // Memastikan status respons adalah 401 (Unauthorized)
        $response->assertJson(['message' => 'Invalid credentials.']); // Memastikan pesan error yang diterima
    }

    // Menguji pengguna dapat login dengan kredensial yang benar
    public function test_user_can_login()
    {
        // Membuat pengguna baru dengan data yang diberikan
        $user = User::factory()->create([
            'email' => 'test@example',
            'password' => bcrypt('password'), // Password yang dienkripsi
            'phone' => '1234567890',
            'address' => 'Jl. Contoh'
        ]);

        // Melakukan login dengan email dan password yang benar
        $response = $this->postLogin([
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200); // Memastikan status respons adalah 200 (OK)

        $token = $response->json('token'); // Mendapatkan token dari respons

        // Menguji akses endpoint yang membutuhkan autentikasi
        $authenticatedResponse = $this->withHeaders([
            'Authorization' => "Bearer $token", // Menyertakan token dalam header
        ])->getJson('/api/user');

        $authenticatedResponse->assertStatus(200); // Memastikan status respons adalah 200 (OK)
        $authenticatedResponse->assertJson(['email' => $user->email]); // Memastikan email pengguna sesuai
    }
}
