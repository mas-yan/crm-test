<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $RoleSuperAdmin = Role::create([
            'name' => 'super_admin',
            'guard_name' => 'api',
        ]);
        Role::create([
            'name' => 'manager',
            'guard_name' => 'api',
        ]);
        Role::create([
            'name' => 'employee',
            'guard_name' => 'api',
        ]);

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superAdmin@example.com',
            'phone' => '12345',
            'address' => 'Semarang',
            'password' => bcrypt('password'),
        ]);

        $superAdmin->assignRole($RoleSuperAdmin);
    }
}
