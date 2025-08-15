<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function showHomePage()
    {
        $pendingUsers = User::where('is_approved', false)->get();
        $approvedUsers = User::where('is_approved', true)
                             ->where('role', '!=', 'Super Admin')
                             ->get();

        return Inertia::render('HomePage', [
            'pendingUsers' => $pendingUsers,
            'approvedUsers' => $approvedUsers,
        ]);
    }
    
    public function approveUser(User $user)
    {
        $user->update(['is_approved' => true]);
        return redirect()->route('home')->with('success', 'Pengguna berhasil disetujui.');
    }
    
    public function rejectUser(User $user)
    {
        $user->delete();
        return redirect()->route('home')->with('success', 'Pengguna berhasil ditolak dan dihapus.');
    }
}
