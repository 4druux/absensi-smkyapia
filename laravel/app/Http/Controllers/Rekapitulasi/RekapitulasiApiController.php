<?php

namespace App\Http\Controllers\Rekapitulasi;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekapitulasiApiController extends Controller
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

    public function getMonths($kelas, $jurusan, $tahun)
    {
        [$startYear, $endYear] = explode('-', $tahun);

        $months = collect(range(7, 12))->map(function ($month) use ($startYear) {
            $date = Carbon::create($startYear, $month, 1);
            return [
                'nama' => $date->translatedFormat('F'),
                'slug' => strtolower($date->translatedFormat('F')),
            ];
        })->merge(collect(range(1, 6))->map(function ($month) use ($endYear) {
            $date = Carbon::create($endYear, $month, 1);
            return [
                'nama' => $date->translatedFormat('F'),
                'slug' => strtolower($date->translatedFormat('F')),
            ];
        }));

        return response()->json($months);
    }
}