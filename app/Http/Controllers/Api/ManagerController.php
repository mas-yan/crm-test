<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function index(Request $request)
    {
        $managers = Manager::with('company.employees')
            ->when($request->input('search'), function ($query, $search) {
                $query->WhereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
            })
            ->when(request('sort'), function ($query, $order) {
                return $query->orderBy(
                    User::select('name')->whereColumn('users.id', 'managers.user_id'),
                    $order
                );
            })
            ->paginate(10);
        return $managers;
    }

    public function detail($id)
    {
        $manager = Manager::with('company.employees')->find($id);
        if ($manager) {
            return response()->json([
                'status' => 'success',
                'message' => 'Detail Manager',
                'data' => $manager
            ]);
        }
        return response()->json(['message' => 'Manager not found'], 404);
    }
}
