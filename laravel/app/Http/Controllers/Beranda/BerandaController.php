<?php

namespace App\Http\Controllers\Beranda;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use App\Models\UangKasOther;
use App\Models\UangKasPayment;
use App\Models\Iuran; 
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class BerandaController extends Controller
{
    private function getMonthNumberFromSlug($slug)
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];
        return $monthMap[strtolower($slug)] ?? null;
    }
    
    private function getCorrectYear($academicYear, $month)
    {
        [$startYear, $endYear] = explode('-', $academicYear);
        return $month >= 7 ? $startYear : $endYear;
    }

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

        $siswa = $selectedKelas->siswas;

        $existingPayments = UangKasPayment::where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->where('minggu', $minggu)
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->siswa_id => ['nominal' => $item->nominal, 'status' => $item->status]];
            })
            ->toArray();
        
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $year = $this->getCorrectYear($tahun, $monthNumber);
        $namaBulan = Carbon::createFromDate($year, $monthNumber, 1)->translatedFormat('F');

        return Inertia::render('Beranda/UangKasPage', [
            'studentData' => [
                'students' => $siswa,
                'classCode' => $kelas,
                'major' => $jurusan,
            ],
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'namaBulan' => $namaBulan,
            'minggu' => $minggu,
            'payments' => $existingPayments,
        ]);
    }

    public function showUangKasOtherPage($kelas, $jurusan, $tahun, $bulanSlug, $iuranId)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        
        $iuranData = Iuran::with('payments')->find($iuranId);

        if (!$iuranData) {
            return redirect()->back()->with('error', 'Iuran tidak ditemukan.');
        }

        $firstPayment = $iuranData->payments->first();
        if (!$firstPayment) {
            return redirect()->back()->with('error', 'Iuran ini belum memiliki data pembayaran.');
        }

        $namaBulan = Carbon::parse($firstPayment->tanggal)->translatedFormat('F');
        
        return Inertia::render('Beranda/UangKasPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                'kelompok' => $selectedKelas->kelompok,
            ],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'namaBulan' => $namaBulan,
            'iuranData' => [
                'id' => $iuranId,
                'deskripsi' => $iuranData->deskripsi,
            ],
            'studentData' => [
                'students' => $selectedKelas->students,
            ],
            'payments' => $iuranData->payments->mapWithKeys(function ($item) {
                return [$item->siswa_id => ['nominal' => $item->nominal, 'status' => $item->status]];
            })->toArray(),
        ]);
    }
}
