<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function createcompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:companies,name',
            'email' => 'required|email|unique:companies,email',
            'phone' => 'required|string|unique:companies,phone',
        ]);
        if (auth()->user()->hasRole('super_admin')) {

            try {
                DB::beginTransaction();
                $company = Company::create($request->all());

                $managerUser = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt('password'), // Default password
                    'phone' => $request->phone,
                    'address' => $request->address
                ]);

                $managerUser->assignRole(['manager', 'employee']);

                $company->managers()->create(['user_id' => $managerUser->id]);

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Company created successfully.', 'data' => $company], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                response()->json(['status' => false, 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'You are not authorized to create a company.',
        ], 403);
    }
}
