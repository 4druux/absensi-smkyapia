<?php

namespace App\Http\Controllers\Permasalahan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use App\Models\PermasalahanKelas;
use App\Models\PermasalahanSiswa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermasalahanController extends Controller
{
    public function selectClass()
    {
        return Inertia::render('Permasalahan/SelectClassPage');
    }

    public function selectYear($kelas, $jurusan)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('Permasalahan/SelectYearPage', [
            'years' => AcademicYear::orderBy('year', 'asc')->get()->map(fn ($y) => ['nomor' => $y->year . '-' . ($y->year + 1)]),
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
        ]);
    }

    public function showYear($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('Permasalahan/SelectProblemTypePage', [
            'tahun' => $tahun,
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
        ]);
    }

    public function showClassProblems($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $problems = PermasalahanKelas::where('kelas_id', $selectedKelas->id)
            ->where('tahun', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        return Inertia::render('Permasalahan/ShowClassProblemsPage', [
            'tahun' => $tahun,
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'problems' => $problems,
        ]);
    }

    public function showStudentProblems($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $studentIds = $selectedKelas->siswas->pluck('id');

        $problems = PermasalahanSiswa::whereIn('siswa_id', $studentIds)
            ->where('tahun', $tahun)
            ->with('siswa')
            ->orderBy('tanggal', 'desc')
            ->get();
            
        return Inertia::render('Permasalahan/ShowStudentProblemsPage', [
            'tahun' => $tahun,
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'students' => $selectedKelas->siswas()->orderBy('nama')->get(),
            'problems' => $problems,
        ]);
    }
}