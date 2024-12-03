<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_company()
    {
        $this->withoutExceptionHandling();
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $token = auth()->login($superAdmin);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/create/company', [
                'name' => 'Company A',
                'email' => 'admin@companya.com',
                'phone' => '123456789',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Company A',
                    'email' => 'admin@companya.com',
                ]
            ]);
    }

    public function test_non_super_admin_cannot_create_company()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create(['role' => 'manager']);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/create/company', [
                'name' => 'Company B',
                'email' => 'admin@companyb.com',
                'phone' => '987654321',
            ]);

        $response->assertStatus(403); // Forbidden
    }
}
