<?php

namespace App\Http\Controllers\Indisipliner;

use App\Http\Controllers\Controller;
use App\Exports\Indisipliner\IndisiplinerExport;
use App\Models\Indisipliner;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;

class IndisiplinerExportController extends Controller
{
    public function exportExcel($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)
            ->firstOrFail();

        $indisiplinerData = Indisipliner::whereHas('siswa', fn($query) => $query->where('kelas_id', $selectedKelas->id))
            ->with(['siswa', 'details']) 
            ->where('tahun', $tahun)
            ->get();
        
        if ($indisiplinerData->isEmpty()) {
            return response()->json([
                'error' => "Tidak ada data indisipliner di kelas {$selectedKelas->nama_kelas} {$selectedKelas->kelompok} - {$selectedKelas->jurusan->nama_jurusan}."
            ], 404);
        }

        $fileName = "Indisipliner-{$selectedKelas->nama_kelas}-{$selectedKelas->kelompok}-{$tahun}.xlsx";

        return Excel::download(new IndisiplinerExport($selectedKelas, $indisiplinerData, $tahun), $fileName);
    }
    
    public function exportPdf($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)
            ->firstOrFail();
            
        $indisiplinerData = Indisipliner::whereHas('siswa', fn($query) => $query->where('kelas_id', $selectedKelas->id))
            ->with(['siswa', 'details']) 
            ->where('tahun', $tahun)
            ->orderBy('tanggal_surat', 'asc') 
            ->get();
        
        if ($indisiplinerData->isEmpty()) {
            return response()->json([
                'error' => "Tidak ada data indisipliner di kelas {$selectedKelas->nama_kelas} {$selectedKelas->kelompok} - {$selectedKelas->jurusan->nama_jurusan}."
            ], 404);
        }
        
        $uniqueOtherViolations = $indisiplinerData->flatMap(function($indisipliner) {
            return $indisipliner->details->whereNotIn('jenis_pelanggaran', ['Terlambat', 'Alfa', 'Bolos']);
        })->unique('jenis_pelanggaran')->pluck('jenis_pelanggaran')->values();

        $data = [
            'selectedClass' => $selectedKelas,
            'tahun' => $tahun,
            'indisiplinerData' => $indisiplinerData,
            'logoPath' => 'images/logo-smk.png',
            'uniqueOtherViolations' => $uniqueOtherViolations,
            'maxOtherViolations' => $uniqueOtherViolations->count(),
        ];
        
        $pdf = Pdf::loadView('exports.indisipliner.indisipliner', $data)
                  ->setPaper('a4', 'landscape');
        
        
        $fileName = "Indisipliner-{$selectedKelas->nama_kelas}-{$selectedKelas->kelompok}-{$tahun}.pdf";
        return $pdf->download($fileName);
    }

    public function exportStudentPdf($siswaId, $tahun, $noUrut)
    {
        $siswa = Siswa::with(['kelas.jurusan'])->findOrFail($siswaId);
        
        $indisiplinerData = Indisipliner::with('details')
            ->where('siswa_id', $siswaId)
            ->orderBy('tanggal_surat', 'asc')
            ->get();

        if ($indisiplinerData->isEmpty()) {
            return response()->json([
                'error' => "Tidak ada data indisipliner untuk siswa {$siswa->nama} di tahun ajaran {$tahun}."
            ], 404);
        }
        
        $uniqueOtherViolations = $indisiplinerData->flatMap(function($indisipliner) {
            return $indisipliner->details->whereNotIn('jenis_pelanggaran', ['Terlambat', 'Alfa', 'Bolos']);
        })->unique('jenis_pelanggaran')->pluck('jenis_pelanggaran')->values();

        $data = [
            'siswa' => $siswa,
            'tahun' => $tahun,
            'noUrut' => $noUrut,
            'indisiplinerData' => $indisiplinerData,
            'logoPath' => 'images/logo-smk.png',
            'uniqueOtherViolations' => $uniqueOtherViolations,
            'maxOtherViolations' => $uniqueOtherViolations->count(),
        ];

        $pdf = Pdf::loadView('exports.indisipliner.indisipliner-siswa', $data)
                  ->setPaper('a4', 'portrait');

        $fileName = "Indisipliner-Siswa-{$siswa->nama}-{$tahun}.pdf";
        return $pdf->download($fileName);
    }
}
