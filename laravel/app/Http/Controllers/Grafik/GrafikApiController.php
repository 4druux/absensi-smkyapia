<?php

namespace App\Http\Controllers\Grafik;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GrafikApiController extends Controller
{
    public function getClasses()
    {
        $classes = Kelas::whereHas('siswas')->with('jurusan')->get();
        
        return response()->json($classes->map(fn($c) => [
            'id' => $c->id,
            'kelas' => $c->nama_kelas,
            'jurusan' => $c->jurusan->nama_jurusan,
            'kelompok' => $c->kelompok,
        ]));
    }

    public function getYears()
    {
        $years = AcademicYear::orderBy('year', 'asc')->get();

        if ($years->isEmpty()) {
            $currentYear = now()->month >= 7 ? now()->year : now()->year - 1;
            AcademicYear::create(['year' => $currentYear]);
            $years = AcademicYear::orderBy('year', 'asc')->get();
        }
        
        return response()->json($years->map(fn ($y) => ['nomor' => $y->year . '-' . ($y->year + 1)]));
    }

    public function storeYearApi(Request $request)
    {
        $latestYear = AcademicYear::orderBy('year', 'desc')->first();
        $yearToCreate = now()->month >= 7 ? now()->year : now()->year - 1;
    
        if ($latestYear) {
            $yearToCreate = $latestYear->year + 1;
        }

        $academicYear = AcademicYear::firstOrCreate(['year' => $yearToCreate]);
    
        return response()->json(['message' => 'Tahun ajaran berhasil ditambahkan!', 'year' => $academicYear], 201);
    }

    public function getYearlyData($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        
        $studentIds = $selectedKelas->siswas()->pluck('id');

        if ($studentIds->isEmpty()) {
            return response()->json(['error' => 'Tidak ada siswa di kelas ini.'], 404);
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

        return response()->json([
            'labels' => $labels,
            'telat' => $telatData,
            'alfa' => $alfaData,
            'sakit' => $sakitData,
            'izin' => $izinData,
            'bolos' => $bolosData,
        ]);
    }
}