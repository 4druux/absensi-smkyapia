<?php

namespace App\Http\Controllers\Absensi;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Holiday;
use App\Exports\AbsensiExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;


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

    public function exportMonthExcel($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        
        $students = $selectedKelas->siswas->pluck('id');
        $absensiCount = Absensi::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $monthNumber)
            ->whereIn('siswa_id', $students)
            ->count();
        
        if ($absensiCount === 0) {
            $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
            return response()->json(['error' => "Tidak ada data absensi untuk bulan {$namaBulan} {$tahun}."], 404);
        }

        $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
        $fileName = "Absensi-{$kelas}-{$jurusan}-{$namaBulan}-{$tahun}.xlsx";

        return Excel::download(new AbsensiExport($kelas, $jurusan, $tahun, $bulanSlug), $fileName);
    }
    
    public function exportMonthPdf($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber) {
            return response()->json(['error' => 'Bulan tidak valid.'], 404);
        }
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $students = $selectedKelas->siswas;
        
        if ($students->isEmpty()) {
            return response()->json(['error' => "Tidak ada siswa di kelas {$kelas} {$jurusan}."], 404);
        }

        $absensiCount = Absensi::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $monthNumber)
            ->whereIn('siswa_id', $students->pluck('id'))
            ->count();

        if ($absensiCount === 0) {
            $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
            return response()->json(['error' => "Tidak ada data absensi untuk bulan {$namaBulan} {$tahun}."], 404);
        }

        $daysInMonth = Carbon::createFromDate($tahun, $monthNumber)->daysInMonth;
        $absensiData = Absensi::whereIn('siswa_id', $students->pluck('id'))
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $monthNumber)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->siswa_id . '_' . Carbon::parse($item->tanggal)->day => $item->status,
                ];
            });

        $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
        $fileName = "Absensi-{$kelas}-{$jurusan}-{$namaBulan}-{$tahun}.pdf";

        $dbHolidays = Holiday::whereYear('date', $tahun)
            ->whereMonth('date', $monthNumber)
            ->pluck('date');
        $weekends = collect();
        $date = Carbon::create($tahun, $monthNumber, 1);
        for ($i = 0; $i < $daysInMonth; $i++) {
            if ($date->isWeekend()) {
                $weekends->push($date->format('Y-m-d'));
            }
            $date->addDay();
        }
        $allHolidays = $dbHolidays->merge($weekends)->unique()->map(fn($date) => Carbon::parse($date)->day);

        $logoPath = 'images/logo-smk.png'; 
        
        $pdf = Pdf::loadView('exports.absensi', compact('students', 'kelas', 'jurusan', 'namaBulan', 'tahun', 'daysInMonth', 'absensiData', 'allHolidays', 'monthNumber', 'logoPath'));
        
        return $pdf->download($fileName);
    }
}