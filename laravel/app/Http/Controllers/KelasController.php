<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->orderBy('kelompok')->get();
        return response()->json($kelas);
    }

    public function show(Kelas $kelas)
    {
        $kelas->load(['jurusan', 'siswas' => function ($query) {
            $query->orderBy('nama');
        }]);
        return response()->json($kelas);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'kelompok' => 'required|string|max:255',
            'jurusan_id' => 'required|exists:jurusans,id',
        ]);

        $kelas = Kelas::create($validatedData);
        $kelas->load('jurusan');
        return response()->json(['message' => 'Kelas berhasil ditambahkan.', 'kelas' => $kelas], 201);
    }

    public function destroy(Kelas $kelas)
    {
        try {
            DB::transaction(function () use ($kelas) {
                $kelas->siswas()->forceDelete();
                $kelas->forceDelete();
            });

            return response()->json(['message' => 'Kelas dan semua siswa berhasil dihapus.']);

        } catch (\Exception $e) {
            Log::error('Gagal menghapus kelas: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server saat menghapus kelas.'], 500);
        }
    }
}