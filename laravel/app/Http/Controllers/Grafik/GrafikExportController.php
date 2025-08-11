<?php

namespace App\Http\Controllers\Grafik;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Exports\Grafik\GrafikExportYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class GrafikExportController extends Controller
{
    private function getChartData($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        
        $studentIds = $selectedKelas->siswas()->pluck('id');

        if ($studentIds->isEmpty()) {
            return null;
        }

        [$startYear, $endYear] = explode('-', $tahun);
        
        $bulanPeriode = collect(range(7, 12))->map(fn($m) => ['month' => $m, 'year' => (int)$startYear])
            ->merge(collect(range(1, 6))->map(fn($m) => ['month' => $m, 'year' => (int)$endYear]));
        
        $labels = $bulanPeriode->map(fn($b) => Carbon::create($b['year'], $b['month'])->translatedFormat('M'));
        
        $telatData = collect();
        $alfaData = collect();
        $sakitData = collect();
        $izinData = collect();
        $bolosData = collect();

        foreach ($bulanPeriode as $bulan) {
            $monthlyData = Absensi::whereIn('siswa_id', $studentIds)
                ->whereYear('tanggal', $bulan['year'])
                ->whereMonth('tanggal', $bulan['month'])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            $telatData->push($monthlyData->get('telat', 0));
            $alfaData->push($monthlyData->get('alfa', 0));
            $sakitData->push($monthlyData->get('sakit', 0));
            $izinData->push($monthlyData->get('izin', 0));
            $bolosData->push($monthlyData->get('bolos', 0));
        }

        return [
            'labels' => $labels,
            'telat' => $telatData,
            'alfa' => $alfaData,
            'sakit' => $sakitData,
            'izin' => $izinData,
            'bolos' => $bolosData,
        ];
    }

    public function exportYearExcel($kelas, $jurusan, $tahun)
    {
        $chartData = $this->getChartData($kelas, $jurusan, $tahun);
        if (!$chartData) {
            return response()->json(['error' => "Tidak ada data untuk diekspor."], 404);
        }

        return Excel::download(
            new GrafikExportYear($chartData, $kelas, $jurusan, $tahun),
            "Grafik-Absensi-{$kelas}-{$jurusan}-{$tahun}.xlsx"
        );
    }

    public function exportYearPdf($kelas, $jurusan, $tahun)
    {
        $chartData = $this->getChartData($kelas, $jurusan, $tahun);
        if (!$chartData) {
            return response()->json(['error' => "Tidak ada data untuk diekspor."], 404);
        }

        $logoPath = 'images/logo-smk.png';
        
        $pdf = Pdf::loadView('exports.grafik.grafik-year', compact(
            'chartData', 'kelas', 'jurusan', 'tahun', 'logoPath'
        ))->setPaper('a4', 'landscape');
        
        return $pdf->download("Grafik-Absensi-{$kelas}-{$jurusan}-{$tahun}.pdf");
    }
}