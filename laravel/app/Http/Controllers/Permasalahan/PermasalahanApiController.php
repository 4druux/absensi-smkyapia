<?php

namespace App\Http\Controllers\Permasalahan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use App\Models\PermasalahanKelas;
use App\Models\PermasalahanSiswa;
use Illuminate\Http\Request;

class PermasalahanApiController extends Controller
{
    public function getClasses()
    {
        $classes = Kelas::whereHas('siswas')->with('jurusan')->get();
        return response()->json($classes->map(fn($c) => ['id' => $c->id, 'kelas' => $c->nama_kelas, 'jurusan' => $c->jurusan->nama_jurusan, 'kelompok' => $c->kelompok,]));
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
    
    public function storeClassProblem(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tahun' => 'required|string',
            'tanggal' => 'required|date',
            'masalah' => 'required|string',
            'pemecahan' => 'required|string',
            'keterangan' => 'required|string',
        ]);
        PermasalahanKelas::create($validated);
        return response()->json(['message' => 'Laporan permasalahan kelas berhasil disimpan!'], 201);
    }

    public function deleteClassProblem($id)
    {
        $problem = PermasalahanKelas::findOrFail($id);
        $problem->delete();
        return response()->json(['message' => 'Laporan berhasil dihapus.']);
    }

    public function storeStudentProblem(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tahun' => 'required|string',
            'tanggal' => 'required|date',
            'masalah' => 'required|string',
            'tindakan_walas' => 'required|string',
            'keterangan' => 'required|string',            
        ]);
        PermasalahanSiswa::create($validated);
        return response()->json(['message' => 'Laporan permasalahan siswa berhasil disimpan!'], 201);
    }

    public function deleteStudentProblem($id)
    {
        $problem = PermasalahanSiswa::findOrFail($id);
        $problem->delete();
        return response()->json(['message' => 'Laporan berhasil dihapus.']);
    }
}