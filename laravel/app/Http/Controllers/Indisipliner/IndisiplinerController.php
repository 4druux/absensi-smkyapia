<?php

namespace App\Http\Controllers\Indisipliner;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndisiplinerController extends Controller
{
    public function selectClass()
    {
        return Inertia::render('Indisipliner/SelectClassPage');
    }

    public function selectYear($kelas, $jurusan)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)
            ->firstOrFail();

        return Inertia::render('Indisipliner/SelectYearPage', [
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
            ->where('nama_kelas', $kelas)
            ->firstOrFail();

        $students = $selectedKelas->siswas()
            ->select('id', 'nama', 'nis')
            ->orderBy('nama')
            ->get();
            
        return Inertia::render('Indisipliner/ShowIndisiplinerPage', [
            'tahun' => $tahun,
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'students' => $students,
        ]);
    }
}