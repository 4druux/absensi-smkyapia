<?php

namespace App\Http\Controllers\Grafik;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GrafikController extends Controller
{
    public function selectClass()
    {
        $classes = Kelas::with('jurusan')->get();
        return Inertia::render('Grafik/SelectClassPage', [
            'classes' => $classes,
        ]);
    }

    public function selectYear($kelas, $jurusan)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)
            ->withCount('siswas')
            ->firstOrFail();

        return Inertia::render('Grafik/SelectYearPage', [
            'years' => AcademicYear::orderBy('year', 'asc')->get()->map(fn ($y) => ['nomor' => $y->year . '-' . ($y->year + 1)]),
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'kelompok' => $selectedKelas->kelompok,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'jumlah_siswa' => $selectedKelas->siswas_count,
            ],
        ]);
    }

    public function showYear($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)
            ->withCount('siswas')
            ->firstOrFail();

        return Inertia::render('Grafik/ShowYearPage', [
            'tahun' => $tahun,
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'kelompok' => $selectedKelas->kelompok,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'jumlah_siswa' => $selectedKelas->siswas_count,
            ],
        ]);
    }
}