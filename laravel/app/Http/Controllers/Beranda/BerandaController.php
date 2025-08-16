<?php

namespace App\Http\Controllers\Beranda;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use App\Models\UangKasOther;
use App\Models\UangKasPayment;
use Inertia\Inertia;

class BerandaController extends Controller
{

   public function selectType($kelas, $jurusan)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('Beranda/SelectTypePage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
        ]);
    }

    public function selectYearAbsensi($kelas, $jurusan)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $years = AcademicYear::orderBy('year', 'asc')->get()->map(fn ($y) => ['nomor' => $y->year . '-' . ($y->year + 1)]);

        return Inertia::render('Beranda/SelectYearPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'years' => $years,
            'type' => 'absensi',
        ]);
    }

    public function selectYearUangKas($kelas, $jurusan)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $years = AcademicYear::orderBy('year', 'asc')->get()->map(fn ($y) => ['nomor' => $y->year . '-' . ($y->year + 1)]);

        return Inertia::render('Beranda/SelectYearPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'years' => $years,
            'type' => 'uang-kas',
        ]);
    }
    
    public function selectMonthAbsensi($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        return Inertia::render('Beranda/SelectMonthPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'tahun' => $tahun,
            'type' => 'absensi',
        ]);
    }
    
    public function selectMonthUangKas($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        return Inertia::render('Beranda/SelectMonthPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'tahun' => $tahun,
            'type' => 'uang-kas',
        ]);
    }
    
    public function selectWeekUangKas($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        return Inertia::render('Beranda/SelectUangKasWeekPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'namaBulan' => ucwords(str_replace('-', ' ', $bulanSlug)),
        ]);
    }

    public function selectDayAbsensi($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        return Inertia::render('Beranda/SelectAbsensiDayPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'namaBulan' => ucwords(str_replace('-', ' ', $bulanSlug)),
        ]);
    }
    
    public function showAbsensiPage($kelas, $jurusan, $tahun, $bulanSlug, $tanggal)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        return Inertia::render('Beranda/AbsensiPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'namaBulan' => ucwords(str_replace('-', ' ', $bulanSlug)),
            'tanggal' => $tanggal,
        ]);
    }
    
    public function showUangKasWeeklyPage($kelas, $jurusan, $tahun, $bulanSlug, $minggu)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        
        return Inertia::render('Beranda/UangKasPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'namaBulan' => ucwords(str_replace('-', ' ', $bulanSlug)),
            'minggu' => $minggu,
            'studentData' => [
                'students' => $selectedKelas->students,
            ],
            'iuranData' => null,
        ]);
    }

    public function showUangKasOtherPage($kelas, $jurusan, $iuranId)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        $iuranData = UangKasOther::with('payments')->find($iuranId);

        return Inertia::render('Beranda/UangKasPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'iuranData' => $iuranData,
            'studentData' => [
                'students' => $selectedKelas->students,
            ],
        ]);
    }
}
