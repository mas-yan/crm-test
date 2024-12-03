<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase; // Menggunakan trait RefreshDatabase untuk memastikan database di-refresh setiap kali pengujian dijalankan

    protected function setUp(): void
    {
        parent::setUp(); // Memanggil metode setUp dari kelas induk
        $this->createRoles(); // Membuat peran yang diperlukan untuk pengujian
    }

    private function createRoles()
    {
        // Membuat peran 'super_admin' dengan guard 'api'
        Role::create(['name' => 'super_admin', 'guard_name' => 'api']);
        // Membuat peran 'employee' dengan guard 'api'
        Role::create(['name' => 'employee', 'guard_name' => 'api']);
        // Membuat peran 'manager' dengan guard 'api'
        Role::create(['name' => 'manager', 'guard_name' => 'api']);
    }

    private function loginAsRole($roleName)
    {
        $user = User::factory()->create(); // Membuat pengguna baru menggunakan factory
        $role = Role::where('name', $roleName)->first(); // Mendapatkan peran berdasarkan nama
        $user->assignRole($role); // Menetapkan peran kepada pengguna
        return auth()->login($user); // Melakukan login dan mengembalikan token autentikasi
    }

    public function test_super_admin_can_create_company()
    {
        $token = $this->loginAsRole('super_admin'); // Login sebagai 'super_admin' dan mendapatkan token

        $response = $this->withHeader('Authorization', "Bearer $token") // Menyertakan token dalam header
            ->postJson('/api/company/create/', [ // Mengirim permintaan POST untuk membuat perusahaan baru
                'name' => 'Company A',
                'email' => 'admin@companya.com',
                'phone' => '123456789',
                'address' => 'hsjdak',
            ]);

        $response->assertStatus(201) // Memastikan respons memiliki status 201 (Created)
            ->assertJson([ // Memastikan data JSON yang diterima sesuai dengan yang diharapkan
                'data' => [
                    'name' => 'Company A',
                    'email' => 'admin@companya.com',
                ]
            ]);
    }

    public function test_non_super_admin_cannot_create_company()
    {
        $token = $this->loginAsRole('manager'); // Login sebagai 'manager' dan mendapatkan token

        $response = $this->withHeader('Authorization', "Bearer $token") // Menyertakan token dalam header
            ->postJson('/api/company/create', [ // Mengirim permintaan POST untuk membuat perusahaan baru
                'name' => 'Company B',
                'email' => 'admin@companyb.com',
                'phone' => '987654321',
            ]);

        $response->assertStatus(403); // Memastikan respons memiliki status 403 (Forbidden)
    }
}
