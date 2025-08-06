<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Siswa;
use App\Models\UangKasPayment;
use Carbon\Carbon;

class UangKasExport implements FromCollection, WithHeadings
{
    protected $kelas;
    protected $jurusan;
    protected $tahun;
    protected $bulanSlug;

    public function __construct($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $this->kelas = $kelas;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;
        $this->bulanSlug = $bulanSlug;
    }

    private function getMonthNumberFromSlug($slug)
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];
        return $monthMap[strtolower($slug)] ?? null;
    }

    public function headings(): array
    {
        $monthNumber = $this->getMonthNumberFromSlug($this->bulanSlug);
        
        $headings = ['Nama Siswa', 'Nominal', 'Minggu ke-1', 'Minggu ke-2', 'Minggu ke-3', 'Minggu ke-4', 'Minggu ke-5'];
        return $headings;
    }

    public function collection()
    {
        $monthNumber = $this->getMonthNumberFromSlug($this->bulanSlug);
        $students = Siswa::where('kelas', $this->kelas)
            ->where('jurusan', $this->jurusan)
            ->orderBy('nama')
            ->get();
        
        $uangKasData = UangKasPayment::whereIn('siswa_id', $students->pluck('id'))
            ->where('tahun', $this->tahun)
            ->where('bulan_slug', $this->bulanSlug)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->siswa_id . '_' . $item->minggu => $item->status,
                ];
            });

        $exportData = collect();
        
        foreach ($students as $student) {
            $rowData = [$student->nama, 0]; 
            $nominal = 0;
            for ($week = 1; $week <= 5; $week++) {
                $status = $uangKasData->get($student->id . '_' . $week, '-');
                if ($status === 'paid') {
                    $nominal = UangKasPayment::where('siswa_id', $student->id)
                                ->where('tahun', $this->tahun)
                                ->where('bulan_slug', $this->bulanSlug)
                                ->where('minggu', $week)
                                ->first()
                                ->nominal ?? 0;
                    $rowData[] = 'Paid';
                } else {
                    $rowData[] = '-';
                }
            }
            $rowData[1] = $nominal;
            $exportData->push($rowData);
        }

        return $exportData;
    }
}