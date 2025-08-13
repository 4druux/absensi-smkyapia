<?php

namespace App\Http\Controllers\UangKas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pengeluaran;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index(Request $request, $kelas, $jurusan, $tahun, $bulanSlug)
    {
        $selectedKelas = Kelas::with('jurusan')->whereHas('jurusan', function ($q) use ($jurusan) {
            $q->where('nama_jurusan', $jurusan);
        })->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('UangKas/PengeluaranPage', [
        'tahun' => $tahun,
        'bulanSlug' => $bulanSlug,
        'selectedClass' => [
            'id' => $selectedKelas->id,
            'kelas' => $selectedKelas->nama_kelas,
            'kelompok' => $selectedKelas->kelompok,
            'jurusan' => $selectedKelas->jurusan->nama_jurusan,
        ],
    ]);;
    }

    public function store(Request $request, $kelas, $jurusan)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
        ]);

        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        Pengeluaran::create([
            'kelas_id' => $selectedKelas->id,
            'tanggal' => $validated['tanggal'],
            'deskripsi' => $validated['deskripsi'],
            'nominal' => $validated['nominal'],
        ]);

        return redirect()->back()->with('success', 'Pengajuan pengeluaran berhasil dibuat.');
    }
}