<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase; // Menggunakan trait RefreshDatabase untuk memastikan database di-refresh sebelum setiap tes

    protected function setUp(): void
    {
        parent::setUp(); // Memanggil setUp dari parent class
        $this->createRoles(); // Membuat peran yang diperlukan untuk tes
    }

    private function createRoles()
    {
        Role::create(['name' => 'manager', 'guard_name' => 'api']); // Membuat peran 'manager' dengan guard 'api'
    }

    private function createUserWithRole($roleName)
    {
        $user = User::factory()->create(); // Membuat user baru menggunakan factory
        $user->assignRole($roleName); // Menetapkan peran ke user
        return $user; // Mengembalikan user yang telah dibuat
    }

    private function createCompany()
    {
        return Company::factory(['name' => "dev"])->create(); // Membuat dan mengembalikan perusahaan baru dengan nama 'dev'
    }

    private function createEmployee($userId, $companyId)
    {
        return Employee::factory()->create(['user_id' => $userId, 'company_id' => $companyId]); // Membuat dan mengembalikan karyawan baru dengan user_id dan company_id yang diberikan
    }

    public function testIndexReturnsEmployees()
    {
        $user = $this->createUserWithRole('manager'); // Membuat user dengan peran 'manager'
        $company = $this->createCompany(); // Membuat perusahaan baru
        $this->createEmployee($user->id, $company->id); // Membuat karyawan baru

        $response = $this->actingAs($user)->getJson('/api/employees'); // Mengirim permintaan GET ke endpoint '/api/employees' sebagai user

        $response->assertStatus(200) // Memastikan respons memiliki status 200
            ->assertJsonStructure([ // Memastikan struktur JSON respons sesuai
                'data' => [
                    '*' => ['name', 'phone', 'address'],
                ],
            ]);
    }

    public function testIndexWithSearch()
    {
        // Membuat user dan employee
        $user = User::factory()->create(['name' => 'John Doe']); // Membuat user dengan nama 'John Doe'
        User::factory()->create(['name' => 'Bob']); // Membuat user lain dengan nama 'Bob'
        $user->assignRole('manager'); // Menetapkan peran 'manager' ke user
        $company = Company::factory(['name' => "dev"])->create(); // Membuat perusahaan baru
        $employee = Employee::factory()->create(['user_id' => $user->id, 'company_id' => $company->id]); // Membuat karyawan baru

        // Memanggil endpoint index dengan parameter search
        $response = $this->actingAs($user)->getJson('/api/employees?search=John'); // Mengirim permintaan GET dengan parameter pencarian 'John'

        // Memastikan response berhasil dan data sesuai
        $response->assertStatus(200) // Memastikan respons memiliki status 200
            ->assertJsonFragment(['name' => 'John Doe']); // Memastikan respons mengandung fragmen JSON dengan nama 'John Doe'
    }

    public function testIndexWithSoftDeleted()
    {
        $user = $this->createUserWithRole('manager'); // Membuat user dengan peran 'manager'
        $company = $this->createCompany(); // Membuat perusahaan baru
        $employee = $this->createEmployee($user->id, $company->id); // Membuat karyawan baru

        $employee->delete(); // Menghapus karyawan (soft delete)

        $response = $this->actingAs($user)->getJson('/api/employees?deleted=true'); // Mengirim permintaan GET dengan parameter 'deleted=true'

        $response->assertStatus(200); // Memastikan respons memiliki status 200
    }

    public function testShowReturnsEmployeeById()
    {
        $user = $this->createUserWithRole('manager'); // Membuat user dengan peran 'manager'
        $company = $this->createCompany(); // Membuat perusahaan baru
        $employee = $this->createEmployee($user->id, $company->id); // Membuat karyawan baru

        $response = $this->actingAs($user)->getJson("/api/employees/{$employee->id}"); // Mengirim permintaan GET untuk mendapatkan karyawan berdasarkan ID

        $response->assertStatus(200) // Memastikan respons memiliki status 200
            ->assertJson(['id' => $employee->id]); // Memastikan respons mengandung ID karyawan yang benar
    }

    public function testStoreCreatesNewEmployee()
    {
        $user = $this->createUserWithRole('manager'); // Membuat user dengan peran 'manager'
        $this->actingAs($user); // Mengatur user sebagai pengguna yang sedang aktif

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]; // Data untuk membuat karyawan baru

        $response = $this->postJson('/api/employees/create', $data); // Mengirim permintaan POST untuk membuat karyawan baru

        $response->assertStatus(201) // Memastikan respons memiliki status 201
            ->assertJson(['message' => 'Employee created successfully']); // Memastikan respons mengandung pesan sukses
    }

    public function testUpdateModifiesEmployee()
    {
        $user = $this->createUserWithRole('manager'); // Membuat user dengan peran 'manager'
        $company = $this->createCompany(); // Membuat perusahaan baru
        $employee = $this->createEmployee($user->id, $company->id); // Membuat karyawan baru
        $this->actingAs($user); // Mengatur user sebagai pengguna yang sedang aktif

        $data = [
            'user_id' => $employee->user_id,
            'company_id' => $employee->company_id,
        ]; // Data untuk memperbarui karyawan

        $response = $this->putJson("/api/employees/{$employee->id}", $data); // Mengirim permintaan PUT untuk memperbarui karyawan

        $response->assertStatus(200) // Memastikan respons memiliki status 200
            ->assertJson(['id' => $employee->id]); // Memastikan respons mengandung ID karyawan yang benar
    }

    public function testRestoreRestoresSoftDeletedEmployee()
    {
        $user = $this->createUserWithRole('manager'); // Membuat user dengan peran 'manager'
        $company = $this->createCompany(); // Membuat perusahaan baru
        $employee = $this->createEmployee($user->id, $company->id); // Membuat karyawan baru
        $this->actingAs($user); // Mengatur user sebagai pengguna yang sedang aktif

        $employee->delete(); // Menghapus karyawan (soft delete)

        $response = $this->patchJson("/api/employees/{$employee->id}/restore"); // Mengirim permintaan PATCH untuk memulihkan karyawan

        $response->assertStatus(200) // Memastikan respons memiliki status 200
            ->assertJson(['message' => 'Employee restored']); // Memastikan respons mengandung pesan sukses
    }

    public function testForceDeletePermanentlyDeletesEmployee()
    {
        $user = $this->createUserWithRole('manager'); // Membuat user dengan peran 'manager'
        $company = $this->createCompany(); // Membuat perusahaan baru
        $employee = $this->createEmployee($user->id, $company->id); // Membuat karyawan baru
        $this->actingAs($user); // Mengatur user sebagai pengguna yang sedang aktif

        $employee->delete(); // Menghapus karyawan (soft delete)

        $response = $this->deleteJson("/api/employees/{$employee->id}/force"); // Mengirim permintaan DELETE untuk menghapus karyawan secara permanen

        $response->assertStatus(200) // Memastikan respons memiliki status 200
            ->assertJson(['message' => 'Employee permanently deleted']); // Memastikan respons mengandung pesan sukses
    }
}
