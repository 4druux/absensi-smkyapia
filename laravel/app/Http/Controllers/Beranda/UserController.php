<?php

namespace App\Http\Controllers\Beranda;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function showHomePage()
    {
        $user = Auth::user();
        $pendingUsers = [];
        $approvedUsers = [];

        if ($user && $user->role === 'Super Admin') {
            $pendingUsers = User::where('is_approved', false)
                ->where('role', '!=', 'Super Admin')
                ->get();

            $approvedUsers = User::where('is_approved', true)
                ->where('role', '!=', 'Super Admin')
                ->get();
        }

        return Inertia::render('Beranda/HomePage', [
            'initialPendingUsers' => $pendingUsers,
            'initialApprovedUsers' => $approvedUsers,
        ]);
    }
}