<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManagerControllerTest extends TestCase
{
    use RefreshDatabase; // Menggunakan trait RefreshDatabase untuk memastikan database di-refresh sebelum setiap tes

    protected function setUp(): void
    {
        parent::setUp(); // Memanggil metode setUp dari kelas induk
        Role::create(['name' => 'manager', 'guard_name' => 'api']); // Membuat role 'manager' dengan guard 'api'
    }

    private function createManager($name)
    {
        // Membuat user baru dengan nama yang diberikan dan memberikan peran 'manager'
        $user = User::factory()->create(['name' => $name])->assignRole('manager');
        // Membuat perusahaan baru dengan nama yang sama
        $company = Company::factory(['name' => $name])->create();
        // Membuat manager baru yang terhubung dengan user dan perusahaan yang baru dibuat
        Manager::factory()->create(['user_id' => $user->id, 'company_id' => $company->id]);
        // Mengembalikan token autentikasi setelah login
        return auth()->login($user);
    }

    private function manager($name)
    {
        // Membuat user baru dengan nama yang diberikan dan memberikan peran 'manager'
        $user = User::factory()->create(['name' => $name])->assignRole('manager');
        // Membuat perusahaan baru dengan nama yang sama
        $company = Company::factory(['name' => $name])->create();
        // Membuat manager baru yang terhubung dengan user dan perusahaan yang baru dibuat
        $manager = Manager::factory()->create(['user_id' => $user->id, 'company_id' => $company->id]);
        // Mengembalikan token autentikasi setelah login
        return $manager;
    }

    public function testIndexReturnsManagers()
    {
        // Membuat manager dengan nama 'Alice' dan mendapatkan token autentikasi
        $token = $this->createManager('Alice');

        // Mengirimkan permintaan GET ke endpoint '/api/managers' dengan header Authorization
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/managers');

        // Memastikan respons memiliki status 200
        $response->assertStatus(200);
        // Memastikan jumlah data dalam respons adalah 1
        $response->assertJsonCount(1, 'data');
    }

    public function testIndexWithSearch()
    {
        // Membuat manager dengan nama 'John' dan mendapatkan token autentikasi
        $token = $this->createManager('John');
        // Membuat manager lain dengan nama 'Bob'
        $this->createManager('Bob');

        // Mengirimkan permintaan GET ke endpoint '/api/managers' dengan parameter pencarian 'John'
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/managers?search=John');

        // Memastikan respons memiliki status 200
        $response->assertStatus(200);
        // Memastikan jumlah data dalam respons adalah 1
        $response->assertJsonCount(1, 'data');
    }

    public function testIndexWithSorting()
    {
        // Membuat manager dengan nama 'Alice' dan mendapatkan token autentikasi
        $token = $this->createManager('Alice');
        // Membuat manager lain dengan nama 'Bob'
        $this->createManager('Bob');

        // Mengirimkan permintaan GET ke endpoint '/api/managers' dengan parameter pengurutan berdasarkan nama secara descending
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/managers?sort_by=name&sort_order=desc');

        // Memastikan respons memiliki status 200
        $response->assertStatus(200);
        // Memastikan urutan data pertama dalam respons adalah perusahaan dengan nama 'Alice'
        $response->assertJsonPath('data.0.company.name', 'Alice');
        // Memastikan urutan data kedua dalam respons adalah perusahaan dengan nama 'Bob'
        $response->assertJsonPath('data.1.company.name', 'Bob');
    }

    public function testDetailManagerExists()
    {
        // Membuat data manager
        $token = $this->createManager('Bob');
        $man = $this->manager('dev');
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/managers/' . $man->id);

        // Memastikan response sukses dan data manager dikembalikan
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Detail Manager',
            ]);
    }

    public function testDetailManagerNotFound()
    {
        $token = $this->createManager('Bob');
        // Memanggil endpoint detail dengan ID yang tidak ada
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/managers/99');

        // Memastikan response 404 dan pesan error
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Manager not found',
            ]);
    }
}
