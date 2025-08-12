<?php

namespace App\Http\Controllers\Rekapitulasi;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\Holiday;
use App\Exports\Rekapitulasi\RekapitulasiExportMonth;
use App\Exports\Rekapitulasi\RekapitulasiExportYear;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class RekapitulasiExportController extends Controller
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

    private function getAnnualActiveDays($tahun)
    {
        [$startYear, $endYear] = explode('-', $tahun);
        $startDate = Carbon::create($startYear, 7, 1);
        $endDate = Carbon::create($endYear, 6, 30);

        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'));
        
        $activeDays = 0;
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && !$holidays->contains($date->format('Y-m-d'))) {
                $activeDays++;
            }
        }
        return $activeDays;
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

            $rekapData->push([
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
            ]);
        }
        
        return $rekapData;
    }

    private function getRekapitulasiDataYear($kelas, $jurusan, $tahun, $annualActiveDays)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $students = $selectedKelas->siswas;
        if ($students->isEmpty()) return collect();

        [$startYear, $endYear] = explode('-', $tahun);
        
        $periods = [
            'Juli - September' => [Carbon::create($startYear, 7, 1)->startOfMonth(), Carbon::create($startYear, 9, 1)->endOfMonth()],
            'Juli - Desember'  => [Carbon::create($startYear, 7, 1)->startOfMonth(), Carbon::create($startYear, 12, 1)->endOfMonth()],
            'Januari - Maret'  => [Carbon::create($endYear, 1, 1)->startOfMonth(), Carbon::create($endYear, 3, 1)->endOfMonth()],
            'Januari - Juni'   => [Carbon::create($endYear, 1, 1)->startOfMonth(), Carbon::create($endYear, 6, 1)->endOfMonth()],
            'Juli - Juni'      => [Carbon::create($startYear, 7, 1)->startOfMonth(), Carbon::create($endYear, 6, 1)->endOfMonth()],
        ];

        $rekapData = collect();

        foreach ($students as $student) {
            $studentData = ['nama' => $student->nama, 'nis' => $student->nis, 'periods' => []];
            $startAcademicDate = Carbon::create($startYear, 7, 1)->startOfMonth();

            foreach ($periods as $name => $dates) {
                $absensiPeriodeIni = Absensi::where('siswa_id', $student->id)
                    ->whereBetween('tanggal', [$dates[0], $dates[1]])
                    ->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status');

                $absensiKumulatif = Absensi::where('siswa_id', $student->id)
                    ->whereBetween('tanggal', [$startAcademicDate, $dates[1]])
                    ->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status');

                $telatPoints = ($absensiKumulatif->get('telat', 0)) * 0.5;
                $alfaPoints  = ($absensiKumulatif->get('alfa', 0)) * 1.5;
                $bolosPoints = ($absensiKumulatif->get('bolos', 0)) * 2;
                $totalPointKumulatif = $telatPoints + $alfaPoints + $bolosPoints;
                
                $studentData['periods'][$name] = [
                    'telat' => $absensiPeriodeIni->get('telat', 0), 'alfa' => $absensiPeriodeIni->get('alfa', 0),
                    'sakit' => $absensiPeriodeIni->get('sakit', 0), 'izin' => $absensiPeriodeIni->get('izin', 0),
                    'bolos' => $absensiPeriodeIni->get('bolos', 0), 'total_point_kumulatif' => $totalPointKumulatif,
                ];
            }

            $absensiTahunan = $studentData['periods']['Juli - Juni'];
            $totalAbsenSIA = $absensiTahunan['sakit'] + $absensiTahunan['izin'] + $absensiTahunan['alfa'] + $absensiTahunan['bolos'];
            $totalAbsenAlfa = $absensiTahunan['alfa']; 

            $studentData['persentase_sia'] = $annualActiveDays > 0 ? (($annualActiveDays - $totalAbsenSIA) / $annualActiveDays) * 100 : 0;
            $studentData['persentase_efektif'] = $annualActiveDays > 0 ? (($annualActiveDays - $totalAbsenAlfa) / $annualActiveDays) * 100 : 0;
            
            $rekapData->push($studentData);
        }
        return $rekapData;
    }

    public function exportYearExcel($kelas, $jurusan, $tahun)
    {
        $annualActiveDays = $this->getAnnualActiveDays($tahun);
        $rekapData = $this->getRekapitulasiDataYear($kelas, $jurusan, $tahun, $annualActiveDays);

        if ($rekapData->isEmpty()) {
            return response()->json(['error' => "Tidak ada data untuk diekspor."], 404);
        }

        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
        ->where('nama_kelas', $kelas)->firstOrFail();
        $kelompok = $selectedKelas->kelompok;

        return Excel::download(
            new RekapitulasiExportYear($rekapData, $kelas, $kelompok, $jurusan, $tahun),
            "Rekapitulasi-Tahunan-{$kelas} {$kelompok}-{$jurusan}-{$tahun}.xlsx"
        );
    }

    public function exportYearPdf($kelas, $jurusan, $tahun)
    {
        $annualActiveDays = $this->getAnnualActiveDays(tahun: $tahun);
        $rekapData = $this->getRekapitulasiDataYear($kelas, $jurusan, $tahun, $annualActiveDays);
        
        if ($rekapData->isEmpty()) {
            return response()->json(['error' => "Tidak ada data rekapitulasi untuk diekspor."], 404);
        }

        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
        ->where('nama_kelas', $kelas)->firstOrFail();
        $kelompok = $selectedKelas->kelompok;

        $logoPath = 'images/logo-smk.png';
        
        $pdf = Pdf::loadView('exports.rekapitulasi.rekap-year', compact(
            'rekapData', 'kelas','kelompok', 'jurusan', 'tahun', 'logoPath', 'annualActiveDays'
        ))->setPaper('a3', 'landscape');
        
        return $pdf->download("Rekapitulasi-Tahunan-{$kelas} {$kelompok}-{$jurusan}-{$tahun}.pdf");
    }

    public function exportMonthExcel($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $rekapData = $this->getRekapitulasiDataMonth($kelas, $jurusan, $tahun, $bulanSlug);
        if ($rekapData->isEmpty()) {
            return response()->json(['error' => "Tidak ada data untuk diekspor."], 404);
        }
        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
        ->where('nama_kelas', $kelas)->firstOrFail();
        $kelompok = $selectedKelas->kelompok;

        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $year = $this->getCorrectYear($tahun, $monthNumber);
        $daysInMonth = Carbon::create($year, $monthNumber)->daysInMonth;
        
        $dbHolidays = Holiday::whereYear('date', $year)->whereMonth('date', $monthNumber)->pluck('date');
        
        $totalHolidays = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $monthNumber, $day);
            if ($date->isWeekend() || $dbHolidays->contains($date->toDateString())) {
                $totalHolidays++;
            }
        }
        $activeDaysInMonth = $daysInMonth - $totalHolidays;
        $namaBulan = Carbon::create($year, $monthNumber, 1)->translatedFormat('F');

        return Excel::download(
            new RekapitulasiExportMonth($rekapData, $kelas, $kelompok, $jurusan, $tahun, "{$namaBulan} {$year}", $activeDaysInMonth),
            "Rekapitulasi-Bulanan-{$kelas} {$kelompok}-{$jurusan}-{$namaBulan}-{$year}.xlsx"
        );
    }

    public function exportMonthPdf($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $rekapData = $this->getRekapitulasiDataMonth($kelas, $jurusan, $tahun, $bulanSlug);
        if ($rekapData->isEmpty()) {
            return response()->json(['error' => "Tidak ada data rekapitulasi untuk diekspor."], 404);
        }

        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
        ->where('nama_kelas', $kelas)->firstOrFail();
        $kelompok = $selectedKelas->kelompok;

        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $year = $this->getCorrectYear($tahun, $monthNumber);
        $daysInMonth = Carbon::create($year, $monthNumber)->daysInMonth;
        
        $dbHolidays = Holiday::whereYear('date', $year)->whereMonth('date', $monthNumber)->pluck('date');
        
        $totalHolidays = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $monthNumber, $day);
            if ($date->isWeekend() || $dbHolidays->contains($date->toDateString())) {
                $totalHolidays++;
            }
        }
        $activeDaysInMonth = $daysInMonth - $totalHolidays;
        $namaBulan = Carbon::create($year, $monthNumber, 1)->translatedFormat('F');
        $logoPath = 'images/logo-smk.png';

        $pdf = Pdf::loadView('exports.rekapitulasi.rekap-month', compact(
            'rekapData',
            'kelas',
            'kelompok',
            'jurusan',
            'tahun',
            'namaBulan',
            'year',
            'activeDaysInMonth',
            'logoPath'
        ))->setPaper('a4', 'landscape');
        
        return $pdf->download("Rekapitulasi-Bulanan-{$kelas} {$kelompok}-{$jurusan}-{$namaBulan}-{$year}.pdf");
    }
}