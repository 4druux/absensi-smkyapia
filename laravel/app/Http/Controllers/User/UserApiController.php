<?php

namespace App\Http\Controllers\User;

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
        $user->update(['is_approved' => true]);
        return response()->json(['message' => 'Pengguna berhasil disetujui.']);
    }

    public function rejectUser(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Pengguna berhasil ditolak dan dihapus.']);
    }
}