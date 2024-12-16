<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'role' => 'required|in:admin,guru,siswa',
            'password' => 'required_if:role,admin|string|min:6', // Hanya diperlukan untuk admin
            'jurusan' => 'required_if:role,siswa|string', // Untuk siswa
            'kelas' => 'required_if:role,siswa|string',   // Untuk siswa
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        // Jika role adalah admin, kita memeriksa password
        if ($request->role === 'admin') {
            $user = User::where('nama', $request->nama)->where('role', $request->role)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401); // Unauthorized
            }

            // Generate token untuk admin
            $token = $user->createToken('admin-token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
            ]);
        }

        // Untuk guru dan siswa, jika tidak ada user, kita buat user baru
        $user = User::where('nama', $request->nama)->where('role', $request->role)->first();

        if (!$user) {
            // Untuk siswa, kita perlu jurusan dan kelas
            if ($request->role === 'siswa') {
                $user = User::create([
                    'nama' => $request->nama,
                    'role' => $request->role,
                    'jurusan' => $request->jurusan,
                    'kelas' => $request->kelas,
                    'password' => '', // Password kosong untuk siswa
                ]);
            } else {
                // Untuk guru, tidak memerlukan jurusan dan kelas
                $user = User::create([
                    'nama' => $request->nama,
                    'role' => $request->role,
                    'password' => '', // Password kosong untuk guru
                ]);
            }
        }

        // Untuk guru dan siswa, tetap memberikan token meskipun tanpa password
        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        // Pastikan hanya admin yang bisa mendaftar admin baru
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized, invalid token.'], 401); // Unauthorized jika token invalid
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:users', // Pastikan nama admin belum ada
            'password' => 'required|string|min:6|regex:/[a-z]/|regex:/[0-9]/',  // Password harus memenuhi persyaratan keamanan
        ]);

        // Jika validasi gagal, kembalikan error
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 400); // Bad Request jika validasi gagal
        }

        // Cek apakah admin dengan nama yang sama sudah terdaftar
        $apakahAdaAdmin = User::where('nama', $request->nama)->where('role', 'admin')->first();
        if ($apakahAdaAdmin) {
            return response()->json(['message' => 'Nama admin sudah terdaftar.'], 409); // Conflict jika nama sudah ada
        }

        try {
            // Membuat admin baru
            $admin = User::create([
                'nama' => $request->nama,
                'role' => 'admin', // Menetapkan role admin
                'password' => Hash::make($request->password), // Hash password menggunakan bcrypt
            ]);

            // Membuat token untuk admin yang baru
            $token = $admin->createToken('admin-token')->plainTextToken;

            // Mengembalikan respons dengan token
            return response()->json([
                'message' => 'Admin registered successfully',
                'token' => $token,
            ], 201); // Created

        } catch (\Exception $e) {
            // Menangani kesalahan yang terjadi saat pembuatan admin
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi admin.',
                'error' => $e->getMessage(), // Pesan error dari exception
            ], 500); // Internal Server Error
        }
    }
}
