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
}
