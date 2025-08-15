<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/LoginPage', [
            'csrf_token' => csrf_token(),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if ($user->role !== 'Super Admin' && !$user->is_approved) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error', 'Akun Anda belum disetujui. Silakan tunggu persetujuan dari Super Admin.');
            }

            $request->session()->regenerate();
            return redirect()->intended('/beranda')->with('success', 'Selamat datang!');
        }

        return back()->with('error', 'Email atau password yang Anda masukkan salah.')->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}