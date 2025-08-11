<?php

namespace App\Http\Controllers\Rekapitulasi;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class RekapitulasiController extends Controller
{
    public function selectClass()
    {
        return Inertia::render('Rekapitulasi/SelectClassPage');
    }

    public function selectYear($kelas, $jurusan)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('Rekapitulasi/SelectYearPage', [
            'years' => AcademicYear::orderBy('year', 'asc')->get()->map(fn ($y) => ['nomor' => $y->year . '-' . ($y->year + 1)]),
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'kelompok' => $selectedKelas->kelompok,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
        ]);
    }

    public function selectMonth($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('Rekapitulasi/SelectMonthPage', [
            'tahun' => $tahun,
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'kelompok' => $selectedKelas->kelompok,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
        ]);
    }

    public function showMonth($kelas, $jurusan, $tahun, $bulanSlug)
    {
        return Inertia::render('Rekapitulasi/ShowMonthPage', [
            'message' => "Halaman detail untuk {$bulanSlug} - {$tahun} sedang dalam pengembangan."
        ]);
    }
}