<?php

namespace App\Http\Controllers\Kenaikan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\KenaikanBersyarat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class KenaikanController extends Controller
{
    public function selectClass()
    {
        return Inertia::render('Kenaikan/SelectClassPage');
    }

    public function selectYear($kelas, $jurusan)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('Kenaikan/SelectYearPage', [
            'years' => AcademicYear::orderBy('year', 'asc')->get()->map(fn ($y) => ['nomor' => $y->year . '-' . ($y->year + 1)]),
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'kelompok' => $selectedKelas->kelompok,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
        ]);
    }

    public function showYear($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        $studentIds = $selectedKelas->siswas()->pluck('id');

       $studentsWithData = KenaikanBersyarat::whereIn('siswa_id', $studentIds)
            ->where('tahun', $tahun)
            ->pluck('siswa_id');

        $students = $selectedKelas->siswas()->orderBy('nama')->get()->map(function ($student) use ($studentsWithData) {
            $student->has_kenaikan_data = $studentsWithData->contains($student->id);
            return $student;
        });
            
        return Inertia::render('Kenaikan/ShowStudentListPage', [
            'tahun' => $tahun,
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'kelompok' => $selectedKelas->kelompok,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
            'students' => $students,
        ]);
    }

 
    public function showStudentForm($kelas, $jurusan, $tahun, Siswa $siswa)
    {
        $siswa->load('kelas.jurusan');

        return Inertia::render('Kenaikan/ShowStudentFormPage', [
            'tahun' => $tahun,
            'selectedClass' => [
                'id' => $siswa->kelas->id,
                'kelas' => $siswa->kelas->nama_kelas,
                'kelompok' => $siswa->kelas->kelompok,
                'jurusan' => $siswa->kelas->jurusan->nama_jurusan,
            ],
            'student' => $siswa,
        ]);
    }
}