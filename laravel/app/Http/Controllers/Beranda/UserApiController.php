<?php

namespace App\Http\Controllers\Beranda;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function getPendingUsers()
    {
        $users = User::where('is_approved', false)
            ->where('role', '!=', 'Super Admin')
            ->get();
            
        return response()->json($users);
    }

    public function getApprovedUsers()
    {
        $users = User::where('is_approved', true)
            ->where('role', '!=', 'Super Admin')
            ->get();
            
        return response()->json($users);
    }

    public function approveUser(User $user)
    {
        try {
            $user->update(['is_approved' => true]);
            return response()->json(['message' => 'Pengguna berhasil disetujui.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan di server.'], 500);
        }
    }

    public function rejectUser(User $user)
    {
        try {
            $user->delete();
            return response()->json(['message' => 'Pengguna berhasil ditolak dan dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan di server.'], 500);
        }
    }
}