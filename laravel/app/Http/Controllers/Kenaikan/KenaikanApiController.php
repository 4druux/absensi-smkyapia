<?php

namespace App\Http\Controllers\Kenaikan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Absensi;
use App\Models\KenaikanBersyarat;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class KenaikanApiController extends Controller
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

    public function getStudentData(Siswa $siswa, $tahun)
    {
        [$startYear, $endYear] = explode('-', $tahun);
        $startAcademicDate = Carbon::create($startYear, 7, 1)->startOfMonth();
        $endAcademicDate = Carbon::create($endYear, 6, 30)->endOfMonth();

        $kehadiranNonAlfa = Absensi::where('siswa_id', $siswa->id)
            ->whereBetween('tanggal', [$startAcademicDate, $endAcademicDate])
            ->whereIn('status', ['hadir', 'telat'])
            ->count();
            
        $savedData = KenaikanBersyarat::where('siswa_id', $siswa->id)
            ->where('tahun', $tahun)
            ->first();

        return response()->json([
            'kehadiranNonAlfa' => $kehadiranNonAlfa,
            'savedData' => $savedData,
        ]);
    }

    public function storeStudentData(Request $request, Siswa $siswa, $tahun)
    {
        $validatedData = $request->validate([
            'jumlah_nilai_kurang' => 'required|integer|min:0',
            'akhlak' => ['required', Rule::in(['Baik', 'Kurang'])],
            'rekomendasi_walas' => ['required', Rule::in(['Tidak Naik', 'Ragu-ragu'])],
            'keputusan_akhir' => 'required|string|max:1000',
        ]);

        KenaikanBersyarat::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tahun' => $tahun,
            ],
            $validatedData
        );

        return response()->json(['message' => 'Data kenaikan bersyarat berhasil disimpan!'], 200);
    }
}