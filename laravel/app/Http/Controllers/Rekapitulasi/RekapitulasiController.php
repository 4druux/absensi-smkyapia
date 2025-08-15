<?php

namespace App\Http\Controllers\Rekapitulasi;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaNoteRekap;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapitulasiController extends Controller
{
    private function getMonthNumberFromSlug($slug)
    {
        $monthMap = ['januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12];
        return $monthMap[strtolower($slug)] ?? null;
    }

    private function getCorrectYear($academicYear, $month)
    {
        [$startYear, $endYear] = explode('-', $academicYear);
        return $month >= 7 ? $startYear : $endYear;
    }

    private function getRekapitulasiDataMonth($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $students = $selectedKelas->siswas;
        if ($students->isEmpty()) {
            return collect();
        }

        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $year = $this->getCorrectYear($tahun, $monthNumber);
        [$startYear, $endYear] = explode('-', $tahun);

        $rekapData = collect();

        foreach ($students as $student) {
            $absensiBulanan = Absensi::where('siswa_id', $student->id)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $monthNumber)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            $totalPointBulanLalu = 0;

            if ($monthNumber != 7) {
                $startAcademicDate = Carbon::create($startYear, 7, 1)->startOfMonth();
                $endPreviousMonthDate = Carbon::create($year, $monthNumber, 1)->subDay()->endOfDay();

                $absensiSebelumnya = Absensi::where('siswa_id', $student->id)
                    ->whereBetween('tanggal', [$startAcademicDate, $endPreviousMonthDate])
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status');

                $telatPoints = ($absensiSebelumnya->get('telat', 0)) * 0.5;
                $alfaPoints = ($absensiSebelumnya->get('alfa', 0)) * 1.5;
                $bolosPoints = ($absensiSebelumnya->get('bolos', 0)) * 2;
                
                $totalPointBulanLalu = $telatPoints + $alfaPoints + $bolosPoints;
            }

            $note = SiswaNoteRekap::where('siswa_id', $student->id)
                                    ->where('tahun', $tahun)
                                    ->where('bulan_slug', $bulanSlug)
                                    ->first();

            $rekapData->push([
                'id' => $student->id,
                'nama' => $student->nama,
                'nis' => $student->nis,
                'absensi' => [
                    'sakit' => $absensiBulanan->get('sakit', 0),
                    'izin' => $absensiBulanan->get('izin', 0),
                    'alfa' => $absensiBulanan->get('alfa', 0),
                    'bolos' => $absensiBulanan->get('bolos', 0),
                    'telat' => $absensiBulanan->get('telat', 0),
                ],
                'total_point_bulan_lalu' => $totalPointBulanLalu,
                'poin_tambahan' => $note ? $note->poin_tambahan : 0,
                'keterangan' => $note ? $note->keterangan : '',
            ]);
        }
        
        return $rekapData;
    }

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
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $studentsData = $this->getRekapitulasiDataMonth($kelas, $jurusan, $tahun, $bulanSlug);

        return Inertia::render('Rekapitulasi/ShowMonthPage', [
            'students' => $studentsData,
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'kelompok' => $selectedKelas->kelompok,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
        ]);
    }

    public function storeStudentNote(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tahun' => 'required|string',
            'bulan_slug' => 'required|string',
            'poin_tambahan' => 'nullable|numeric',
            'keterangan' => 'nullable|string',
        ]);

        SiswaNoteRekap::updateOrCreate(
            [
                'siswa_id' => $request->siswa_id,
                'tahun' => $request->tahun,
                'bulan_slug' => $request->bulan_slug,
            ],
            [
                'poin_tambahan' => $request->poin_tambahan,
                'keterangan' => $request->keterangan,
            ]
        );

        return response()->json(['message' => 'Poin dan keterangan berhasil disimpan.'], 200);
    }
}