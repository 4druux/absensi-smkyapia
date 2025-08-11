<?php

namespace App\Http\Controllers\Permasalahan;

use App\Exports\Permasalahan\PermasalahanCombinedExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PermasalahanKelas;
use App\Models\PermasalahanSiswa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PermasalahanExportController extends Controller
{
    private function getCombinedProblemsData($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $permasalahanKelas = PermasalahanKelas::where('kelas_id', $selectedKelas->id)
            ->where('tahun', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();
            
        $studentIds = $selectedKelas->siswas->pluck('id');
        $permasalahanSiswa = PermasalahanSiswa::whereIn('siswa_id', $studentIds)
            ->where('tahun', $tahun)
            ->with('siswa')
            ->orderBy('tanggal', 'desc')
            ->get();

        return [
            'kelas' => $permasalahanKelas,
            'siswa' => $permasalahanSiswa,
        ];
    }

    public function exportCombinedExcel($kelas, $jurusan, $tahun)
    {
        $data = $this->getCombinedProblemsData($kelas, $jurusan, $tahun);

        if ($data['kelas']->isEmpty() && $data['siswa']->isEmpty()) {
            return response()->json(['error' => "Tidak ada data permasalahan untuk diekspor."], 404);
        }

        return Excel::download(
            new PermasalahanCombinedExport($data, $kelas, $jurusan, $tahun),
            "Laporan-Permasalahan-{$kelas}-{$jurusan}-{$tahun}.xlsx"
        );
    }

    public function exportCombinedPdf($kelas, $jurusan, $tahun)
    {
        $data = $this->getCombinedProblemsData($kelas, $jurusan, $tahun);

        if ($data['kelas']->isEmpty() && $data['siswa']->isEmpty()) {
            return response()->json(['error' => "Tidak ada data permasalahan untuk diekspor."], 404);
        }
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        $logoPath = 'images/logo-smk.png';

        $pdf = Pdf::loadView('exports.permasalahan.permasalahan-combined', [
            'permasalahanKelas' => $data['kelas'],
            'permasalahanSiswa' => $data['siswa'],
            'kelas' => $selectedKelas->nama_kelas,
            'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            'tahun' => $tahun,
            'logoPath' => $logoPath,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Laporan-Permasalahan-{$kelas}-{$jurusan}-{$tahun}.pdf");
    }
}