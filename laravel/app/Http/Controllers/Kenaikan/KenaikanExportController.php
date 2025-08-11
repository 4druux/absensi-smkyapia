<?php

namespace App\Http\Controllers\Kenaikan;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\KenaikanBersyarat;
use App\Exports\Kenaikan\KenaikanExport;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class KenaikanExportController extends Controller
{
    private function getKenaikanData($kelas, $jurusan, $tahun)
    {
        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $students = $selectedKelas->siswas;
        if ($students->isEmpty()) {
            return collect();
        }

        [$startYear, $endYear] = explode('-', $tahun);
        $startAcademicDate = Carbon::create($startYear, 7, 1)->startOfMonth();
        $endAcademicDate = Carbon::create($endYear, 6, 30)->endOfMonth();

        $studentsToReport = collect();

        foreach ($students as $student) {
            $savedData = KenaikanBersyarat::where('siswa_id', $student->id)
                ->where('tahun', $tahun)
                ->first();

            if ($savedData) {
                $kehadiranNonAlfa = Absensi::where('siswa_id', $student->id)
                    ->whereBetween('tanggal', [$startAcademicDate, $endAcademicDate])
                    ->whereIn('status', ['hadir', 'telat'])
                    ->count();

                $studentsToReport->push([
                    'nama' => $student->nama,
                    'nis' => $student->nis,
                    'kehadiran_non_alfa' => $kehadiranNonAlfa,
                    'jumlah_nilai_kurang' => $savedData->jumlah_nilai_kurang,
                    'akhlak' => $savedData->akhlak,
                    'rekomendasi_walas' => $savedData->rekomendasi_walas,
                    'keputusan_akhir' => $savedData->keputusan_akhir,
                ]);
            }
        }
        return $studentsToReport->sortBy('nama');
    }

    public function exportExcel($kelas, $jurusan, $tahun)
    {
        $data = $this->getKenaikanData($kelas, $jurusan, $tahun);

        if ($data->isEmpty()) {
            return response()->json(['error' => "Tidak ada siswa yang memenuhi kriteria untuk diekspor."], 404);
        }

        return Excel::download(
            new KenaikanExport($data, $kelas, $jurusan, $tahun),
            "Kenaikan-Bersyarat-{$kelas}-{$jurusan}-{$tahun}.xlsx"
        );
    }

    public function exportPdf($kelas, $jurusan, $tahun)
    {
        $data = $this->getKenaikanData($kelas, $jurusan, $tahun);
        
        if ($data->isEmpty()) {
            return response()->json(['error' => "Tidak ada siswa yang memenuhi kriteria untuk diekspor."], 404);
        }

        $logoPath = 'images/logo-smk.png';

        $pdf = Pdf::loadView('exports.kenaikan.kenaikan-bersyarat', compact('data', 'kelas', 'jurusan', 'tahun', 'logoPath'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->download("Kenaikan-Bersyarat-{$kelas}-{$jurusan}-{$tahun}.pdf");
    }
}