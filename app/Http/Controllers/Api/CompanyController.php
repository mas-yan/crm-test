<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function createcompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:companies,name',
            'email' => 'required|email|unique:companies,email',
            'phone' => 'required|string|unique:companies,phone',
        ]);
        $company = Company::create($request->all());

        // Create manager account for the company
        $managerUser = User::create([
            'name' => 'Default Manager',
            'email' => $request->email,
            'password' => bcrypt('password123'), // Default password
            'role' => 'manager',
        ]);

        $company->managers()->create(['user_id' => $managerUser->id]);

        return response()->json(['data' => $company], 201);
    }
}
