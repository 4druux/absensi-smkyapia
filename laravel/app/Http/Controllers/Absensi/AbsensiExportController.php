<?php

namespace App\Http\Controllers\Absensi; 

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Holiday;
use App\Exports\Absensi\AbsensiExportMonth;
use App\Exports\Absensi\AbsensiExportYear; 
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AbsensiExportController extends Controller
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

    public function exportMonthExcel($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $year = $this->getCorrectYear($tahun, $monthNumber);
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        
        $students = $selectedKelas->siswas->pluck('id');
        $absensiCount = Absensi::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $monthNumber)
            ->whereIn('siswa_id', $students)
            ->count();
        
        $namaBulan = Carbon::createFromDate($year, $monthNumber)->translatedFormat('F');

        if ($absensiCount === 0) {
            return response()->json(['error' => "Tidak ada data absensi untuk bulan {$namaBulan} {$year}."], 404);
        }

        $fileName = "Absensi-{$kelas}-{$jurusan}-{$namaBulan}-{$year}.xlsx";

        return Excel::download(new AbsensiExportMonth($kelas, $jurusan, $year, $bulanSlug), $fileName);
    }
    
    public function exportMonthPdf($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber) {
            return response()->json(['error' => 'Bulan tidak valid.'], 404);
        }
        
        $year = $this->getCorrectYear($tahun, $monthNumber);
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        
        $students = $selectedKelas->siswas;
        $namaBulan = Carbon::createFromDate($year, $monthNumber)->translatedFormat('F');

        if ($students->isEmpty()) {
            return response()->json(['error' => "Tidak ada data siswa di kelas {$kelas} {$jurusan}."], 404);
        }

        $absensiCount = Absensi::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $monthNumber)
            ->whereIn('siswa_id', $students->pluck('id'))
            ->count();

        if ($absensiCount === 0) {
            return response()->json(['error' => "Tidak ada data absensi untuk bulan {$namaBulan} {$year}."], 404);
        }

        $daysInMonth = Carbon::createFromDate($year, $monthNumber)->daysInMonth;
        $absensiData = Absensi::whereIn('siswa_id', $students->pluck('id'))
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $monthNumber)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->siswa_id . '_' . Carbon::parse($item->tanggal)->day => $item->status,
                ];
            });

        $fileName = "Absensi-{$kelas}-{$jurusan}-{$namaBulan}-{$year}.pdf";

        $dbHolidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $monthNumber)
            ->pluck('date');
        $weekends = collect();
        $date = Carbon::create($year, $monthNumber, 1);
        for ($i = 0; $i < $daysInMonth; $i++) {
            if ($date->isWeekend()) {
                $weekends->push($date->format('Y-m-d'));
            }
            $date->addDay();
        }
        $allHolidays = $dbHolidays->merge($weekends)->unique()->map(fn($date) => Carbon::parse($date)->day);

        $logoPath = 'images/logo-smk.png'; 
        
        $pdf = Pdf::loadView('exports.absensi.absensi-month', compact('students', 'kelas', 'jurusan', 'namaBulan', 'year', 'daysInMonth', 'absensiData', 'allHolidays', 'monthNumber', 'logoPath'));
        
        return $pdf->download($fileName);
    }

    public function exportYearExcel($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        
        $students = $selectedKelas->siswas;

        if ($students->isEmpty()) {
            return response()->json(['error' => "Tidak ada data siswa di kelas {$kelas} {$jurusan}."], 404);
        }

        [$startYear, $endYear] = explode('-', $tahun);
        $bulanPeriode = collect(range(7, 12))->map(fn($m) => ['month' => $m, 'year' => (int)$startYear])
            ->merge(collect(range(1, 6))->map(fn($m) => ['month' => $m, 'year' => (int)$endYear]));

        $rekapAbsensi = [];

        foreach ($students as $student) {
            $studentRekap = ['nama' => $student->nama, 'total_bulanan' => []];
            $totalStudent = ['telat' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'bolos' => 0];

            foreach ($bulanPeriode as $bulan) {
                $totalBulan = Absensi::where('siswa_id', $student->id)
                    ->whereYear('tanggal', $bulan['year'])
                    ->whereMonth('tanggal', $bulan['month'])
                    ->whereIn('status', ['telat', 'sakit', 'izin', 'alfa', 'bolos'])
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $counts = [
                    'telat' => $totalBulan['telat'] ?? 0,
                    'sakit' => $totalBulan['sakit'] ?? 0,
                    'izin' => $totalBulan['izin'] ?? 0,
                    'alfa' => $totalBulan['alfa'] ?? 0,
                    'bolos' => $totalBulan['bolos'] ?? 0,
                ];

                $studentRekap['total_bulanan'][] = ['bulan' => $bulan['month'], 'counts' => $counts];

                $totalStudent['telat'] += $counts['telat'];
                $totalStudent['sakit'] += $counts['sakit'];
                $totalStudent['izin'] += $counts['izin'];
                $totalStudent['alfa'] += $counts['alfa'];
                $totalStudent['bolos'] += $counts['bolos'];
            }
            $studentRekap['total_tahunan'] = $totalStudent;
            $rekapAbsensi[] = $studentRekap;
        }

        if (collect($rekapAbsensi)->every(fn($rekap) => collect($rekap['total_tahunan'])->every(fn($count) => $count == 0))) {
            return response()->json(['error' => "Tidak ada data absensi untuk tahun ajaran {$tahun}."], 404);
        }
        
        $fileName = "Absensi-{$kelas}-{$jurusan}-{$tahun}.xlsx";

        return Excel::download(new AbsensiExportYear($rekapAbsensi, $kelas, $jurusan, $tahun), $fileName);
    }

    public function exportYearPdf($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_kelas', $kelas)->where('nama_jurusan', $jurusan))->firstOrFail();
        
        $students = $selectedKelas->siswas;

        if ($students->isEmpty()) {
            return response()->json(['error' => "Tidak ada data siswa di kelas {$kelas} {$jurusan}."], 404);
        }

        [$startYear, $endYear] = explode('-', $tahun);
        $bulanPeriode = collect(range(7, 12))->map(fn($m) => ['month' => $m, 'year' => (int)$startYear])
            ->merge(collect(range(1, 6))->map(fn($m) => ['month' => $m, 'year' => (int)$endYear]));

        $rekapAbsensi = [];
        $grandTotals = ['telat' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'bolos' => 0];

        foreach ($students as $student) {
            $studentRekap = ['nama' => $student->nama, 'total_bulanan' => []];
            $totalStudent = ['telat' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'bolos' => 0];

            foreach ($bulanPeriode as $bulan) {
                $totalBulan = Absensi::where('siswa_id', $student->id)
                    ->whereYear('tanggal', $bulan['year'])
                    ->whereMonth('tanggal', $bulan['month'])
                    ->whereIn('status', ['telat', 'sakit', 'izin', 'alfa', 'bolos'])
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $counts = [
                    'telat' => $totalBulan['telat'] ?? 0,
                    'sakit' => $totalBulan['sakit'] ?? 0,
                    'izin' => $totalBulan['izin'] ?? 0,
                    'alfa' => $totalBulan['alfa'] ?? 0,
                    'bolos' => $totalBulan['bolos'] ?? 0,
                ];

                $studentRekap['total_bulanan'][] = ['bulan' => $bulan['month'], 'counts' => $counts];

                $totalStudent['telat'] += $counts['telat'];
                $totalStudent['sakit'] += $counts['sakit'];
                $totalStudent['izin'] += $counts['izin'];
                $totalStudent['alfa'] += $counts['alfa'];
                $totalStudent['bolos'] += $counts['bolos'];
            }
            $studentRekap['total_tahunan'] = $totalStudent;
            $rekapAbsensi[] = $studentRekap;

            $grandTotals['telat'] += $totalStudent['telat'];
            $grandTotals['sakit'] += $totalStudent['sakit'];
            $grandTotals['izin'] += $totalStudent['izin'];
            $grandTotals['alfa'] += $totalStudent['alfa'];
            $grandTotals['bolos'] += $totalStudent['bolos'];
        }

        if (collect($rekapAbsensi)->every(fn($rekap) => collect($rekap['total_tahunan'])->every(fn($count) => $count == 0))) {
            return response()->json(['error' => "Tidak ada data absensi untuk tahun ajaran {$tahun}."], 404);
        }

        $namaBulan = Carbon::create(null, 7, 1)->translatedFormat('F') . ' - ' . Carbon::create(null, 6, 1)->translatedFormat('F');
        $fileName = "Absensi-{$kelas}-{$jurusan}-{$tahun}.pdf";
        $logoPath = 'images/logo-smk.png'; 

        $pdf = Pdf::loadView('exports.absensi.absensi-year', compact('rekapAbsensi', 'grandTotals', 'kelas', 'jurusan', 'tahun', 'namaBulan', 'logoPath'));
        
        return $pdf->download($fileName);
    }
}