<?php

namespace App\Http\Controllers\UangKas;

use App\Exports\UangKas\UangKasExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\UangKasPayment;
use App\Models\Holiday;
use App\Models\Iuran;
use App\Models\Pengeluaran;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UangKasExportController extends Controller
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
            
        $students = $selectedKelas->siswas;
        $kelompok = $selectedKelas->kelompok;

        if ($students->isEmpty()) {
            return response()->json(['error' => "Tidak ada data siswa di kelas ini."], 404);
        }

        $uangKasData = UangKasPayment::whereIn('siswa_id', $students->pluck('id'))
            ->where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->get();
        
        $iuranData = Iuran::where('kelas_id', $selectedKelas->id)
            ->with(['payments' => function ($query) use ($year, $monthNumber) {
                $query->whereYear('tanggal', $year)->whereMonth('tanggal', $monthNumber);
            }])
            ->whereHas('payments', function ($query) use ($year, $monthNumber) {
                $query->whereYear('tanggal', $year)->whereMonth('tanggal', $monthNumber);
            })
            ->get();

        $pengeluaranData = Pengeluaran::where('kelas_id', $selectedKelas->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $monthNumber)
            ->where('status', 'approved')
            ->get();
        
        $dbHolidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $monthNumber)
            ->get()
            ->pluck('date');

        $weeksInMonth = [];
        $firstDayOfMonth = Carbon::create($year, $monthNumber, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $startOfWeek = $firstDayOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        
        $weekNumber = 1;
        while ($startOfWeek <= $lastDayOfMonth) {
            $endOfWeek = $startOfWeek->copy()->addDays(6);
            $isWeekHoliday = true;
            $currentDay = $startOfWeek->copy();
            
            while ($currentDay <= $endOfWeek) {
                if ($currentDay->month === $monthNumber && !in_array($currentDay->format('Y-m-d'), $dbHolidays->toArray()) && !$currentDay->isWeekend()) {
                    $isWeekHoliday = false;
                    break;
                }
                $currentDay->addDay();
            }
            
            $weeksInMonth[$weekNumber] = $isWeekHoliday;
            
            $weekNumber++;
            $startOfWeek->addWeek();
        }

        if ($uangKasData->isEmpty() && count(array_filter($weeksInMonth, fn($isHoliday) => !$isHoliday)) > 0) {
            $namaBulan = Carbon::createFromDate($year, $monthNumber)->translatedFormat('F');
            if ($iuranData->isEmpty()) {
            } else {
                return response()->json(['error' => "Tidak ada data uang kas untuk bulan {$namaBulan} {$year}."], 404);
            }
        }

        $namaBulan = Carbon::createFromDate($year, $monthNumber)->translatedFormat('F');
        $fileName = "Uang Kas-{$kelas} {$kelompok}-{$jurusan}-{$namaBulan}-{$year}.xlsx";

        return Excel::download(new UangKasExport($kelas, $kelompok, $jurusan, $tahun, $year, $bulanSlug, $weeksInMonth, $iuranData, $pengeluaranData), $fileName);
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
        $kelompok = $selectedKelas->kelompok;
        $namaBulan = Carbon::createFromDate($year, $monthNumber)->translatedFormat('F');

        if ($students->isEmpty()) {
            return response()->json(['error' => 'Tidak ada data siswa di kelas ini.'], 404);
        }

        $uangKasData = UangKasPayment::whereIn('siswa_id', $students->pluck('id'))
            ->where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->siswa_id . '_' . $item->minggu => $item,
                ];
            });

        $iuranData = Iuran::where('kelas_id', $selectedKelas->id)
            ->with(['payments' => function ($query) use ($year, $monthNumber) {
                $query->whereYear('tanggal', $year)->whereMonth('tanggal', $monthNumber);
            }])
            ->whereHas('payments', function ($query) use ($year, $monthNumber) {
                $query->whereYear('tanggal', $year)->whereMonth('tanggal', $monthNumber);
            })
            ->get();
            
        $pengeluaranData = Pengeluaran::where('kelas_id', $selectedKelas->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $monthNumber)
            ->where('status', 'approved')
            ->get();

        $dbHolidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $monthNumber)
            ->get()
            ->pluck('date');
        
        $weeksInMonth = [];
        $firstDayOfMonth = Carbon::create($year, $monthNumber, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $startOfWeek = $firstDayOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        
        $weekNumber = 1;
        while ($startOfWeek <= $lastDayOfMonth) {
            $endOfWeek = $startOfWeek->copy()->addDays(6);
            $isWeekHoliday = true;
            $currentDay = $startOfWeek->copy();
            
            while ($currentDay <= $endOfWeek) {
                if ($currentDay->month === $monthNumber && !in_array($currentDay->format('Y-m-d'), $dbHolidays->toArray()) && !$currentDay->isWeekend()) {
                    $isWeekHoliday = false;
                    break;
                }
                $currentDay->addDay();
            }
            
            $weeksInMonth[$weekNumber] = $isWeekHoliday;
            
            $weekNumber++;
            $startOfWeek->addWeek();
        }

        $paidWeeksCount = 0;
        foreach ($students as $student) {
            $paidCount = $uangKasData->filter(fn($payment) => $payment->siswa_id === $student->id && $payment->status === 'paid')->count();
            $nonHolidayWeeks = count(array_filter($weeksInMonth, fn($isHoliday) => !$isHoliday));
            if ($paidCount === $nonHolidayWeeks) {
                    $paidWeeksCount++;
            }
        }
        
        if ($uangKasData->isEmpty() && $paidWeeksCount === 0) {
            $namaBulan = Carbon::createFromDate($year, $monthNumber)->translatedFormat('F');
             if ($iuranData->isEmpty()) {
            } else {
                return response()->json(['error' => "Tidak ada data uang kas untuk bulan {$namaBulan} {$year}."], 404);
            }
        }
        
        $fileName = "Uang Kas-{$kelas} {$kelompok}-{$jurusan}-{$namaBulan}-{$year}.pdf";
        $logoPath = 'images/logo-smk.png';

        $pdf = Pdf::loadView('exports.uang-kas.uangkas', compact('students', 'kelas', 'kelompok', 'jurusan', 'namaBulan', 'year', 'uangKasData', 'weeksInMonth', 'logoPath', 'iuranData', 'pengeluaranData'));
        
        return $pdf->download($fileName);
    }
}
