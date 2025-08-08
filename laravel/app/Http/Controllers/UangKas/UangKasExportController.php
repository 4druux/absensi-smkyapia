<?php

namespace App\Http\Controllers\UangKas;

use App\Exports\UangKasExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\UangKasPayment;
use App\Models\Holiday;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

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
    
    public function exportMonthExcel($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        $students = $selectedKelas->siswas;

        if ($students->isEmpty()) {
            $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
            return response()->json(['error' => "Tidak ada data siswa di kelas ini."], 404);
        }

        $uangKasData = UangKasPayment::whereIn('siswa_id', $students->pluck('id'))
            ->where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->get();
        
        $dbHolidays = Holiday::whereYear('date', $tahun)
            ->whereMonth('date', $monthNumber)
            ->get()
            ->pluck('date');

        $weeksInMonth = [];
        $firstDayOfMonth = Carbon::create($tahun, $monthNumber, 1);
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
            $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
            return response()->json(['error' => "Tidak ada data uang kas untuk bulan {$namaBulan}."], 404);
        }

        $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
        $fileName = "UangKas-{$kelas}-{$jurusan}-{$namaBulan}-{$tahun}.xlsx";

        return Excel::download(new UangKasExport($kelas, $jurusan, $tahun, $bulanSlug, $weeksInMonth), $fileName);
    }
    
    public function exportMonthPdf($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        $students = $selectedKelas->siswas;

        if ($students->isEmpty()) {
            return response()->json(['error' => 'Tidak ada siswa di kelas ini.'], 404);
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

        $dbHolidays = Holiday::whereYear('date', $tahun)
            ->whereMonth('date', $monthNumber)
            ->get()
            ->pluck('date');
        
        $weeksInMonth = [];
        $firstDayOfMonth = Carbon::create($tahun, $monthNumber, 1);
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
            $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
            return response()->json(['error' => "Tidak ada data uang kas untuk bulan {$namaBulan}."], 404);
        }
        
        $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
        $fileName = "UangKas-{$kelas}-{$jurusan}-{$namaBulan}-{$tahun}.pdf";
        $logoPath = 'images/logo-smk.png';

        $pdf = Pdf::loadView('exports.uangkas', compact('students', 'kelas', 'jurusan', 'namaBulan', 'tahun', 'uangKasData', 'weeksInMonth', 'logoPath'));
        
        return $pdf->download($fileName);
    }
}