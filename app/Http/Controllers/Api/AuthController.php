<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Memvalidasi permintaan yang masuk
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email', // Email harus ada, bertipe string, dan format email yang valid
            'password' => 'required|string', // Password harus ada dan bertipe string
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            // Mengembalikan respons JSON dengan error validasi dan status 422
            return response()->json($validator->errors(), 422);
        }

        // Mendapatkan kredensial dari permintaan
        $credentials = $request->only('email', 'password');

        // Jika autentikasi gagal
        if (!$token = auth()->guard('api')->attempt($credentials)) {
            // Mengembalikan respons JSON dengan pesan error dan status 401
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // Jika autentikasi berhasil
        return response()->json([
            'success' => true, // Menandakan bahwa autentikasi berhasil
            'user'    => auth()->guard('api')->user(), // Mengambil data pengguna yang terautentikasi
            'token'   => $token // Mengembalikan token autentikasi
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        // Memvalidasi permintaan yang masuk
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', // Nama harus ada, bertipe string, dan maksimal 255 karakter
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->user(), // Email harus ada, bertipe string, format email yang valid, maksimal 255 karakter, dan unik kecuali untuk pengguna yang sedang login
            'phone' => 'required|string|max:15|unique:users,phone,' . auth()->user(), // Nomor telepon harus ada, bertipe string, maksimal 15 karakter, dan unik kecuali untuk pengguna yang sedang login
            'address' => 'string|max:255', // bertipe string, dan maksimal 255 karakter
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            // Mengembalikan respons JSON dengan error validasi dan status 422
            return response()->json($validator->errors(), 422);
        }

        // Mendapatkan pengguna yang sedang login
        $user = auth()->user();

        // Memperbarui data pengguna
        $user->update($request->only('name', 'email', 'phone', 'address'));

        // Mengembalikan respons JSON dengan data pengguna yang diperbarui dan status 200
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => $user
        ], 200);
    }

    public function logout()
    {
        // Mendapatkan pengguna yang sedang login
        $user = auth()->user();

        // Menghapus token autentikasi
        auth()->logout();

        // Mengembalikan respons JSON dengan pesan sukses dan status 200
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.'
        ], 200);
    }
}
