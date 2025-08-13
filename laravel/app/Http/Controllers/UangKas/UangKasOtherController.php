<?php

namespace App\Http\Controllers\UangKas;

use App\Http\Controllers\Controller;
use App\Models\Iuran;
use App\Models\Kelas;
use App\Models\UangKasOther;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UangKasOtherController extends Controller
{
    public function store(Request $request, $kelas, $jurusan)
    {
        $validated = $request->validate([
            'deskripsi' => 'required|string|max:255',
            'tanggal' => 'required|date',
        ]);

        $selectedKelas = Kelas::whereHas('jurusan', function ($query) use ($jurusan) {
            $query->where('nama_jurusan', $jurusan);
        })->where('nama_kelas', $kelas)->firstOrFail();

        $students = $selectedKelas->siswas;

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa di kelas ini.');
        }

        DB::transaction(function () use ($students, $validated, $selectedKelas) {
            $newIuran = Iuran::create([
                'kelas_id' => $selectedKelas->id,
                'deskripsi' => $validated['deskripsi'],
            ]);

            foreach ($students as $student) {
                UangKasOther::create([
                    'iuran_id' => $newIuran->id,
                    'siswa_id' => $student->id,
                    'tanggal' => $validated['tanggal'],
                    'nominal' => 0,
                    'status' => 'unpaid',
                ]);
            }
        });

        return redirect()->back()->with('success', 'Iuran baru berhasil dibuat.');
    }

    public function show(Request $request, $kelas, $jurusan, $tahun, $bulanSlug, Iuran $iuran)
{
    $selectedKelas = $iuran->kelas;
    $studentData = [
        'students' => $selectedKelas->siswas,
    ];

    $firstPayment = $iuran->payments->first();

    if (!$firstPayment) {
        return redirect()->route('uang-kas.month.show', [
            'kelas' => $selectedKelas->nama_kelas,
            'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
        ])->with('error', 'Iuran ini belum memiliki data pembayaran.');
    }

    $namaBulan = $firstPayment->tanggal->translatedFormat('F');

    return Inertia::render('UangKas/UangKasPage', [
        'studentData' => $studentData,
        'tahun' => $tahun,
        'bulanSlug' => $bulanSlug,
        'namaBulan' => $namaBulan,
        'selectedClass' => [
            'id' => $selectedKelas->id,
            'kelas' => $selectedKelas->nama_kelas,
            'kelompok' => $selectedKelas->kelompok,
            'jurusan' => $selectedKelas->jurusan->nama_jurusan,
        ],
        'iuranData' => [
            'id' => $iuran->id,
            'deskripsi' => $iuran->deskripsi,
        ],
    ]);
}
}