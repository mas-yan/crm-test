<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    // 1. Tampilkan Semua Data (termasuk opsi hanya aktif atau soft deleted)
    public function index(Request $request)
    {
        // Opsi filter soft delete
        $query = Employee::query();

        if ($request->input('deleted') === 'true') {
            $query->onlyTrashed(); // Hanya data yang terhapus
        }

        if ($request->has('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%');
            });
        }

        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order');
            $query->orderBy($sortBy, $sortOrder);
        }

        $employees = $query->with(['user', 'company'])->paginate(10);
        $result = $employees->getCollection()->map(function ($employee) {
            return [
                'name' => $employee->user->name,
                'phone' => $employee->user->phone,
                'address' => $employee->user->address,
            ];
        });
        $employees->setCollection($result);

        return response()->json($employees);
    }

    // 2. Tampilkan Data Berdasarkan ID
    public function show($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);

        return response()->json($employee);
    }

    // 3. Simpan Data Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'address' => 'required|string',
        ]);

        try {
            DB::beginTransaction();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt('password'),
                'phone' => $request->phone,
                'address' => $request->address
            ]);

            $user->assignRole('employee');

            $company = Manager::where('user_id', auth()->user()->id)->first()->company_id;

            $employee = Employee::create([
                'user_id' => $user->id,
                'company_id' => $company
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Employee created successfully',
        ], 201);
    }

    // 4. Update Data
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
        ]);

        $employee->update($validated);

        return response()->json($employee);
    }

    // 5. Soft Delete (Hapus Sementara)
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json(['message' => 'Employee soft deleted']);
    }

    // 6. Restore Data yang Dihapus
    public function restore($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->restore();

        return response()->json(['message' => 'Employee restored']);
    }

    // 7. Hapus Permanen
    public function forceDelete($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->forceDelete();

        return response()->json(['message' => 'Employee permanently deleted']);
    }
}
