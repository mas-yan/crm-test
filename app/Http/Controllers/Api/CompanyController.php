<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function createCompany(Request $request)
    {
        $this->validateRequest($request);

        if (!$this->isUserAuthorized()) {
            return $this->unauthorizedResponse();
        }

        try {
            DB::beginTransaction();
            $company = $this->createNewCompany($request);
            $managerUser = $this->createManagerUser($request);
            $this->assignRolesToManager($managerUser);
            $this->linkManagerToCompany($company, $managerUser);
            DB::commit();

            return $this->successResponse($company);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    private function validateRequest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:companies,name',
            'email' => 'required|email|unique:companies,email',
            'phone' => 'required|string|unique:companies,phone',
        ]);
    }

    private function isUserAuthorized()
    {
        return auth()->user()->hasRole('super_admin');
    }

    private function unauthorizedResponse()
    {
        return response()->json([
            'status' => false,
            'message' => 'You are not authorized to create a company.',
        ], 403);
    }

    private function createNewCompany(Request $request)
    {
        return Company::create($request->all());
    }

    private function createManagerUser(Request $request)
    {
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt('password'), // Default password
            'phone' => $request->phone,
            'address' => $request->address
        ]);
    }

    private function assignRolesToManager(User $managerUser)
    {
        $managerUser->assignRole(['manager', 'employee']);
    }

    private function linkManagerToCompany(Company $company, User $managerUser)
    {
        $company->managers()->create(['user_id' => $managerUser->id]);
    }

    private function successResponse(Company $company)
    {
        return response()->json(['status' => true, 'message' => 'Company created successfully.', 'data' => $company], 201);
    }

    private function errorResponse(\Exception $e)
    {
        return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
}
