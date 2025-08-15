<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function showRegister()
    {
        $registeredRoles = User::pluck('role')->unique()->toArray();
        return Inertia::render('Auth/RegisterPage', [
            'registeredRoles' => $registeredRoles,
            'csrf_token' => csrf_token(),
        ]);
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => ['required', 'string', 'in:Super Admin,Admin,Wali Kelas,Bendahara kelas,Guru'],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_approved' => false,
            ]);

            return redirect()->route('home')->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan.');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}